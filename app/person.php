<?php

class Person
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /* ===============================
       CREATE
    =============================== */

    public function create(
        string $voornaam,
        ?string $tussenvoegsels,
        string $achternaam
    ): bool {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare(
                "INSERT INTO person (voornaam, tussenvoegsels, achternaam)
                 VALUES (:voornaam, :tussenvoegsels, :achternaam)"
            );

            $stmt->execute([
                'voornaam'       => $voornaam,
                'tussenvoegsels' => $tussenvoegsels,
                'achternaam'     => $achternaam
            ]);

            $this->pdo->commit();
            return true;

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Person::create error: " . $e->getMessage());
            return false;
        }
    }

    /* ===============================
       READ ALL
    =============================== */

    public function readAll(): array
    {
        try {
            $stmt = $this->pdo->query("SELECT * FROM person ORDER BY id DESC");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Person::readAll error: " . $e->getMessage());
            return [];
        }
    }

    /* ===============================
       READ ONE
    =============================== */

    public function read(int $id): array|false
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM person WHERE id = :id");
            $stmt->execute(['id' => $id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Person::read error: " . $e->getMessage());
            return false;
        }
    }

    /* ===============================
       UPDATE
    =============================== */

    public function update(
        int $id,
        string $voornaam,
        ?string $tussenvoegsels,
        string $achternaam
    ): bool {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare(
                "UPDATE person
                 SET voornaam = :voornaam,
                     tussenvoegsels = :tussenvoegsels,
                     achternaam = :achternaam
                 WHERE id = :id"
            );

            $stmt->execute([
                'id'             => $id,
                'voornaam'       => $voornaam,
                'tussenvoegsels' => $tussenvoegsels,
                'achternaam'     => $achternaam
            ]);

            $this->pdo->commit();
            return true;

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Person::update error: " . $e->getMessage());
            return false;
        }
    }

    /* ===============================
       DELETE
    =============================== */

    public function delete(int $id): bool
    {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare(
                "DELETE FROM person WHERE id = :id"
            );

            $stmt->execute(['id' => $id]);

            $this->pdo->commit();
            return true;

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Person::delete error: " . $e->getMessage());
            return false;
        }
    }
}
