<?php
declare(strict_types=1);

namespace App\Modelos;

use App\Nucleo\BD;
use PDO;

abstract class ModeloBase
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = BD::pdo();
    }
}