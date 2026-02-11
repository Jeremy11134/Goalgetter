<?php

class Trainer
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /* CREATE */
    public function create(int $user_id, int $person_id, int $club_id): bool
    {
        try {
            $this->pdo->beginTransaction();

            $sql = "INSERT INTO trainer (user_id, person_id, club_id)
                    VALUES (:user_id, :person_id, :club_id)";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'user_id'   => $user_id,
                'person_id' => $person_id,
                'club_id'   => $club_id
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
        $stmt = $this->pdo->query("SELECT * FROM trainer ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    /* READ ONE */
    public function read(int $id): array|false
    {
        $stmt = $this->pdo->prepare("SELECT * FROM trainer WHERE id = :id");
        $stmt->execute(['id' => $id]);

        return $stmt->fetch();
    }

    /* UPDATE */
    public function update(
        int $id,
        int $user_id,
        int $person_id,
        int $club_id
    ): bool {
        try {
            $this->pdo->beginTransaction();

            $sql = "UPDATE trainer
                    SET user_id = :user_id,
                        person_id = :person_id,
                        club_id = :club_id
                    WHERE id = :id";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'id'        => $id,
                'user_id'   => $user_id,
                'person_id' => $person_id,
                'club_id'   => $club_id
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
                "DELETE FROM trainer WHERE id = :id"
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
