<?php

class Person
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /* CREATE */
    public function create(
        string $voornaam,
        ?string $tussenvoegsels,
        string $achternaam
    ): bool {
        try {
            $this->pdo->beginTransaction();

            $sql = "INSERT INTO person (voornaam, tussenvoegsels, achternaam)
                    VALUES (:voornaam, :tussenvoegsels, :achternaam)";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'voornaam'       => $voornaam,
                'tussenvoegsels' => $tussenvoegsels,
                'achternaam'     => $achternaam
            ]);

            $this->pdo->commit();
            return true;

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    /* READ ALL */
    public function readAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM person ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    /* READ ONE */
    public function read(int $id): array|false
    {
        $stmt = $this->pdo->prepare("SELECT * FROM person WHERE id = :id");
        $stmt->execute(['id' => $id]);

        return $stmt->fetch();
    }

    /* UPDATE */
    public function update(
        int $id,
        string $voornaam,
        ?string $tussenvoegsels,
        string $achternaam
    ): bool {
        try {
            $this->pdo->beginTransaction();

            $sql = "UPDATE person
                    SET voornaam = :voornaam,
                        tussenvoegsels = :tussenvoegsels,
                        achternaam = :achternaam
                    WHERE id = :id";

            $stmt = $this->pdo->prepare($sql);
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
            return false;
        }
    }

    /* DELETE */
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
            return false;
        }
    }
}
