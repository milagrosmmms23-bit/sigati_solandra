<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use Throwable;

final class Config
{
    private static array $data = [];

    public static function get(string $key, mixed $default = null): mixed
    {
        [$file, $item] = array_pad(explode('.', $key, 2), 2, null);

        if (!isset(self::$data[$file])) {
            $path = dirname(__DIR__, 2).'/config/'.$file.'.php';

            if (!is_file($path)) {
                return $default;
            }

            self::$data[$file] = require $path;
        }

        return $item === null
            ? self::$data[$file]
            : (self::$data[$file][$item] ?? $default);
    }
}

final class DB
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $config = Config::get('database');
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $config['host'],
            $config['port'],
            $config['database'],
            $config['charset']
        );

        self::$pdo = new PDO($dsn, $config['username'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        return self::$pdo;
    }
}

final class Flash
{
    public static function add(string $type, string $message): void
    {
        $_SESSION['_flash'][] = compact('type', 'message');
    }

    public static function success(string $message): void
    {
        self::add('success', $message);
    }

    public static function error(string $message): void
    {
        self::add('danger', $message);
    }

    public static function warning(string $message): void
    {
        self::add('warning', $message);
    }

    public static function take(): array
    {
        $flashes = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);

        return $flashes;
    }
}

final class Csrf
{
    public static function token(): string
    {
        return $_SESSION['_token'] ??= bin2hex(random_bytes(32));
    }

    public static function verify(): void
    {
        $postedToken = (string) ($_POST['_token'] ?? '');

        if ($postedToken === '' || !hash_equals(self::token(), $postedToken)) {
            \abort(419, 'La sesión del formulario expiró.');
        }
    }
}

final class Auth
{
    public static function attempt(string $username, string $password): bool
    {
        $statement = DB::pdo()->prepare(
            'SELECT u.*, r.name role_name
             FROM usuarios u
             JOIN roles r ON r.id = u.role_id
             WHERE u.username = ? AND u.active = 1
             LIMIT 1'
        );
        $statement->execute([$username]);
        $user = $statement->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return false;
        }

        unset($user['password_hash']);
        session_regenerate_id(true);
        $_SESSION['user'] = $user;

        DB::pdo()
            ->prepare('UPDATE usuarios SET last_login_at = NOW() WHERE id = ?')
            ->execute([$user['id']]);

        return true;
    }

    public static function check(): bool
    {
        return isset($_SESSION['user']);
    }

    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function id(): ?int
    {
        return isset($_SESSION['user']['id']) ? (int) $_SESSION['user']['id'] : null;
    }

    public static function role(): ?string
    {
        return $_SESSION['user']['role_name'] ?? null;
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            Flash::warning('Inicia sesión para continuar.');
            \redirect('ingreso');
        }
    }

    public static function requireRole(array $roles): void
    {
        self::requireLogin();

        if (!in_array(self::role(), $roles, true)) {
            \abort(403, 'No tienes permiso.');
        }
    }

    public static function logout(): void
    {
        unset($_SESSION['user']);
        session_regenerate_id(true);
    }
}

final class Audit
{
    public static function log(
        string $module,
        string $action,
        string $entity,
        ?int $id,
        mixed $old = null,
        mixed $new = null
    ): void {
        try {
            $statement = DB::pdo()->prepare(
                'INSERT INTO registros_auditoria
                    (user_id, module, action, entity_type, entity_id, old_values, new_values, ip_address, user_agent, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())'
            );

            $statement->execute([
                Auth::id(),
                $module,
                $action,
                $entity,
                $id,
                $old ? json_encode($old, JSON_UNESCAPED_UNICODE) : null,
                $new ? json_encode($new, JSON_UNESCAPED_UNICODE) : null,
                $_SERVER['REMOTE_ADDR'] ?? null,
                substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
            ]);
        } catch (Throwable) {
            // La auditoría no debe bloquear una operación principal exitosa.
        }
    }
}

final class View
{
    public static function render(string $view, array $data = [], string $layout = 'plantilla'): void
    {
        $file = dirname(__DIR__).'/Views/'.$view.'.php';

        if (!is_file($file)) {
            \abort(500, 'Vista no encontrada: '.$view);
        }

        extract($data, EXTR_SKIP);
        ob_start();
        require $file;
        $content = ob_get_clean();

        if ($layout === '') {
            echo $content;
            return;
        }

        require dirname(__DIR__).'/Views/'.$layout.'.php';
    }

    public static function capture(string $view, array $data = []): string
    {
        extract($data, EXTR_SKIP);
        ob_start();
        require dirname(__DIR__).'/Views/'.$view.'.php';

        return (string) ob_get_clean();
    }
}

abstract class Controller
{
    protected function view(string $view, array $data = [], string $layout = 'plantilla'): void
    {
        View::render($view, $data, $layout);
    }

    protected function validate(array $data, array $rules): array
    {
        $errors = [];

        foreach ($rules as $field => $ruleList) {
            $value = trim((string) ($data[$field] ?? ''));

            foreach (explode('|', $ruleList) as $rule) {
                if ($rule === 'required' && $value === '') {
                    $errors[$field] = 'Campo obligatorio.';
                }

                if ($rule === 'email' && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field] = 'Correo inválido.';
                }

                if (str_starts_with($rule, 'max:') && mb_strlen($value) > (int) substr($rule, 4)) {
                    $errors[$field] = 'Longitud máxima excedida.';
                }
            }
        }

        return $errors;
    }

    protected function errors(array $errors, array $old, string $path): never
    {
        $_SESSION['_errors'] = $errors;
        $_SESSION['_old'] = $old;

        \redirect($path);
    }
}

final class Router
{
    private array $routes = [];

    public function get(string $path, array $handler): void
    {
        $this->routes[] = ['GET', $path, $handler];
    }

    public function post(string $path, array $handler): void
    {
        $this->routes[] = ['POST', $path, $handler];
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
        $base = rtrim((string) Config::get('app.base_url'), '/');

        if ($base !== '' && str_starts_with($uri, $base)) {
            $uri = substr($uri, strlen($base));
        }

        $uri = '/'.trim($uri, '/');

        if ($uri === '//') {
            $uri = '/';
        }

        foreach ($this->routes as [$routeMethod, $path, $handler]) {
            if ($routeMethod !== $method) {
                continue;
            }

            $pattern = '#^'.preg_replace(
                '#\{[a-zA-Z_][a-zA-Z0-9_]*\}#',
                '([^/]+)',
                rtrim($path, '/')
            ).'/?$#';

            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches);
                [$class, $action] = $handler;
                (new $class())->$action(...$matches);
                return;
            }
        }

        \abort(404, 'Ruta no encontrada.');
    }
}
