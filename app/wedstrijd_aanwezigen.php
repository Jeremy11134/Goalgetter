<?php

class WedstrijdAanwezigen
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /* CREATE */
    public function create(
        int $speler_id,
        int $trainer_id,
        string $status
    ): bool {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare(
                "INSERT INTO wedstrijd_aanwezigen (speler_id, trainer_id, status)
                 VALUES (:speler_id, :trainer_id, :status)"
            );

            $stmt->execute([
                'speler_id'  => $speler_id,
                'trainer_id' => $trainer_id,
                'status'     => $status
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
            "SELECT * FROM wedstrijd_aanwezigen ORDER BY id DESC"
        );
        return $stmt->fetchAll();
    }

    /* READ ONE */
    public function read(int $id): array|false
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM wedstrijd_aanwezigen WHERE id = :id"
        );
        $stmt->execute(['id' => $id]);

        return $stmt->fetch();
    }

    /* UPDATE */
    public function update(
        int $id,
        int $speler_id,
        int $trainer_id,
        string $status
    ): bool {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare(
                "UPDATE wedstrijd_aanwezigen
                 SET speler_id = :speler_id,
                     trainer_id = :trainer_id,
                     status = :status
                 WHERE id = :id"
            );

            $stmt->execute([
                'id'         => $id,
                'speler_id'  => $speler_id,
                'trainer_id' => $trainer_id,
                'status'     => $status
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
                "DELETE FROM wedstrijd_aanwezigen WHERE id = :id"
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
