<?php
declare(strict_types=1);

namespace App\Nucleo;

use PDO;
use Throwable;

final class Config
{
    private static array $datos = [];

    public static function obtener(string $clave, mixed $default = null): mixed
    {
        [$archivo, $registro] = array_pad(explode('.', $clave, 2), 2, null);

        if (!isset(self::$datos[$archivo])) {
            $ruta = dirname(__DIR__, 2).'/config/'.$archivo.'.php';

            if (!is_file($ruta)) {
                return $default;
            }

            self::$datos[$archivo] = require $ruta;
        }

        return $registro === null
            ? self::$datos[$archivo]
            : (self::$datos[$archivo][$registro] ?? $default);
    }
}

final class BD
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $config = Config::obtener('base_datos');
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $config['servidor'],
            $config['puerto'],
            $config['nombre'],
            $config['codificacion']
        );

        self::$pdo = new PDO($dsn, $config['usuario'], $config['clave'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        return self::$pdo;
    }
}

final class Flash
{
    public static function add(string $tipo, string $mensaje): void
    {
        $_SESSION['_flash'][] = ['type' => $tipo, 'message' => $mensaje];
    }

    public static function exito(string $mensaje): void
    {
        self::add('success', $mensaje);
    }

    public static function error(string $mensaje): void
    {
        self::add('danger', $mensaje);
    }

    public static function advertencia(string $mensaje): void
    {
        self::add('warning', $mensaje);
    }

    public static function tomar(): array
    {
        $mensajes = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);

        return $mensajes;
    }
}

final class Csrf
{
    public static function token(): string
    {
        return $_SESSION['_token'] ??= bin2hex(random_bytes(32));
    }

    public static function verificar(): void
    {
        $tokenEnviado = (string) ($_POST['_token'] ?? '');

        if ($tokenEnviado === '' || !hash_equals(self::token(), $tokenEnviado)) {
            \abort(419, 'La sesión del formulario expiró.');
        }
    }
}

final class Auth
{
    public static function intentar(string $usuario, string $clave): bool
    {
        $consulta = BD::pdo()->prepare(
            'SELECT u.*, r.nombre nombre_rol
             FROM usuarios u
             JOIN roles r ON r.id = u.rol_id
             WHERE u.usuario = ? AND u.activo = 1
             LIMIT 1'
        );
        $consulta->execute([$usuario]);
        $usuarioEncontrado = $consulta->fetch();

        if (!$usuarioEncontrado || !password_verify($clave, $usuarioEncontrado['clave_hash'])) {
            return false;
        }

        unset($usuarioEncontrado['clave_hash']);
        session_regenerate_id(true);
        $_SESSION['usuario'] = $usuarioEncontrado;

        BD::pdo()
            ->prepare('UPDATE usuarios SET ultimo_ingreso_en = NOW() WHERE id = ?')
            ->execute([$usuarioEncontrado['id']]);

        return true;
    }

    public static function autenticado(): bool
    {
        return isset($_SESSION['usuario']);
    }

    public static function usuario(): ?array
    {
        return $_SESSION['usuario'] ?? null;
    }

    public static function id(): ?int
    {
        return isset($_SESSION['usuario']['id']) ? (int) $_SESSION['usuario']['id'] : null;
    }

    public static function rol(): ?string
    {
        return $_SESSION['usuario']['nombre_rol'] ?? null;
    }

    public static function requerirIngreso(): void
    {
        if (!self::autenticado()) {
            Flash::advertencia('Inicia sesión para continuar.');
            \redirect('ingreso');
        }
    }

    public static function requerirRol(array $roles): void
    {
        self::requerirIngreso();

        if (!in_array(self::rol(), $roles, true)) {
            \abort(403, 'No tienes permiso.');
        }
    }

    public static function cerrarSesion(): void
    {
        unset($_SESSION['usuario']);
        session_regenerate_id(true);
    }
}

final class Auditoria
{
    public static function registrar(
        string $modulo,
        string $accion,
        string $entidad,
        ?int $id,
        mixed $anteriores = null,
        mixed $nuevo = null
    ): void {
        try {
            $consulta = BD::pdo()->prepare(
                'INSERT INTO registros_auditoria
                    (usuario_id, modulo, accion, tipo_entidad, entidad_id, valores_anteriores, valores_nuevos, direccion_ip, navegador, creado_en)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())'
            );

            $consulta->execute([
                Auth::id(),
                $modulo,
                $accion,
                $entidad,
                $id,
                $anteriores ? json_encode($anteriores, JSON_UNESCAPED_UNICODE) : null,
                $nuevo ? json_encode($nuevo, JSON_UNESCAPED_UNICODE) : null,
                $_SERVER['REMOTE_ADDR'] ?? null,
                substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
            ]);
        } catch (Throwable) {
            // La auditoría no debe bloquear una operación principal exitosa.
        }
    }
}

final class Vista
{
    public static function renderizar(string $vista, array $datos = [], string $plantilla = 'plantilla'): void
    {
        $archivo = dirname(__DIR__).'/Vistas/'.$vista.'.php';

        if (!is_file($archivo)) {
            \abort(500, 'Vista no encontrada: '.$vista);
        }

        extract($datos, EXTR_SKIP);
        ob_start();
        require $archivo;
        $contenido = ob_get_clean();

        if ($plantilla === '') {
            echo $contenido;
            return;
        }

        require dirname(__DIR__).'/Vistas/'.$plantilla.'.php';
    }

    public static function capturar(string $vista, array $datos = []): string
    {
        extract($datos, EXTR_SKIP);
        ob_start();
        require dirname(__DIR__).'/Vistas/'.$vista.'.php';

        return (string) ob_get_clean();
    }
}

abstract class Controlador
{
    protected function vista(string $vista, array $datos = [], string $plantilla = 'plantilla'): void
    {
        Vista::renderizar($vista, $datos, $plantilla);
    }

    protected function validar(array $datos, array $reglas): array
    {
        $errores = [];

        foreach ($reglas as $campo => $listaReglas) {
            $valor = trim((string) ($datos[$campo] ?? ''));

            foreach (explode('|', $listaReglas) as $regla) {
                if ($regla === 'required' && $valor === '') {
                    $errores[$campo] = 'Campo obligatorio.';
                }

                if (in_array($regla, ['email', 'correo'], true) && $valor !== '' && !filter_var($valor, FILTER_VALIDATE_EMAIL)) {
                    $errores[$campo] = 'Correo inválido.';
                }

                if (str_starts_with($regla, 'max:') && mb_strlen($valor) > (int) substr($regla, 4)) {
                    $errores[$campo] = 'Longitud máxima excedida.';
                }
            }
        }

        return $errores;
    }

    protected function enviarErrores(array $errores, array $anteriores, string $ruta): never
    {
        $_SESSION['_errors'] = $errores;
        $_SESSION['_old'] = $anteriores;

        \redirect($ruta);
    }
}

final class Router
{
    private array $rutas = [];

    public function get(string $ruta, array $manejador): void
    {
        $this->rutas[] = ['GET', $ruta, $manejador];
    }

    public function post(string $ruta, array $manejador): void
    {
        $this->rutas[] = ['POST', $ruta, $manejador];
    }

    public function dispatch(): void
    {
        $metodo = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
        $base = rtrim((string) Config::obtener('aplicacion.url_base'), '/');

        if ($base !== '' && str_starts_with($uri, $base)) {
            $uri = substr($uri, strlen($base));
        }

        $uri = '/'.trim($uri, '/');

        if ($uri === '//') {
            $uri = '/';
        }

        foreach ($this->rutas as [$metodoRuta, $ruta, $manejador]) {
            if ($metodoRuta !== $metodo) {
                continue;
            }

            $pattern = '#^'.preg_replace(
                '#\{[a-zA-Z_][a-zA-Z0-9_]*\}#',
                '([^/]+)',
                rtrim($ruta, '/')
            ).'/?$#';

            if (preg_match($pattern, $uri, $coincidencias)) {
                array_shift($coincidencias);
                [$clase, $accion] = $manejador;
                (new $clase())->$accion(...$coincidencias);
                return;
            }
        }

        \abort(404, 'Ruta no encontrada.');
    }
}
