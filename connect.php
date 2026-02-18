<?php

class Connect
{
    private PDO $pdo;

    public function __construct()
    {
        $host = "localhost";
        $db   = "goalgetter";
        $user = "root";
        $pass = "";
        $charset = "utf8mb4";

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {

            $this->pdo = new PDO($dsn, $user, $pass, $options);

            // Optional: log succesvolle connectie (voor debug)
            // error_log("Database connected successfully.");

        } catch (PDOException $e) {

            error_log("Database connection failed: " . $e->getMessage());

            // Gooi exception door i.p.v. die()
            throw new PDOException("Database connection failed.");
        }
    }

    public function pdo(): PDO
    {
        return $this->pdo;
    }
}
