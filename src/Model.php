<?php

declare(strict_types=1);

namespace App;

use App\Helpers\ResponseHelper;

class Model
{
    protected static function getConnection(): \PDO
    {
        if (is_null(self::$connection)) {
            try {
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME;
                self::$connection = new \PDO(
                    $dsn,
                    DB_USER,
                    DB_PASS,
                    [
                        \PDO::ATTR_DEFAULT_FETCH_MODE   => \PDO::FETCH_OBJ,
                        \PDO::ATTR_PERSISTENT           => true,
                        \PDO::ERRMODE_EXCEPTION         => true
                    ]
                );
            } catch (\PDOException $e) {
                ResponseHelper::sendStatusCodeAndDie(500, "Internal error: Could not establish connection to database.");
            }
        }

        return self::$connection;
    }

    private static null|\PDO $connection = null;
}
