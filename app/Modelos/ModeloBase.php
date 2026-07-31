<?php
declare(strict_types=1);

namespace App\Modelos;

use App\Nucleo\DB;
use PDO;

abstract class ModeloBase
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = DB::pdo();
    }
}