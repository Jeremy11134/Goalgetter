<?php

class ClubTrainingen
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /* CREATE */
    public function create(int $club_id, int $training_id): bool
    {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare(
                "INSERT INTO club_trainingen (club_id, training_id)
                 VALUES (:club_id, :training_id)"
            );

            $stmt->execute([
                'club_id'     => $club_id,
                'training_id' => $training_id
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
        $stmt = $this->pdo->query(
            "SELECT * FROM club_trainingen ORDER BY id DESC"
        );
        return $stmt->fetchAll();
    }

    /* READ ONE */
    public function read(int $id): array|false
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM club_trainingen WHERE id = :id"
        );
        $stmt->execute(['id' => $id]);

        return $stmt->fetch();
    }

    /* UPDATE */
    public function update(int $id, int $club_id, int $training_id): bool
    {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare(
                "UPDATE club_trainingen
                 SET club_id = :club_id,
                     training_id = :training_id
                 WHERE id = :id"
            );

            $stmt->execute([
                'id'          => $id,
                'club_id'     => $club_id,
                'training_id' => $training_id
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
                "DELETE FROM club_trainingen WHERE id = :id"
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