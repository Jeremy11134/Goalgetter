<?php

class Trainingen
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Nieuwe training; faalt stil als er op dezelfde dag een overlappend tijdslot bestaat.
     */
    public function create(
        string $start,
        string $end,
        string $titel,
        string $date,
        ?string $description,
        string $status
    ): bool {

        if ($this->trainingControle($date, $start, $end)) {
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare(
                "INSERT INTO trainingen
            (start, end, titel, date, description, status)
            VALUES
            (:start, :end, :titel, :date, :description, :status)"
            );

            $stmt->execute([
                'start'       => $start,
                'end'         => $end,
                'titel'       => $titel,
                'date'        => $date,
                'description' => $description,
                'status'      => $status
            ]);

            $this->pdo->commit();
            return true;

        } catch (PDOException $e) {

            $this->pdo->rollBack();
            die($e->getMessage());
        }
    }

    /** Alle trainingen. */
    public function readAll(): array
    {
        try {
            $stmt = $this->pdo->query(
                "SELECT * FROM trainingen ORDER BY date DESC, start"
            );

            return $stmt->fetchAll();

        } catch (PDOException $e) {

            error_log("Trainingen::readAll error: " . $e->getMessage());
            return [];
        }
    }

    /** Eén training op id. */
    public function read(int $id): array|false
    {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT * FROM trainingen WHERE id = :id"
            );

            $stmt->execute(['id' => $id]);

            return $stmt->fetch();

        } catch (PDOException $e) {

            error_log("Trainingen::read error: " . $e->getMessage());
            return false;
        }
    }

    /** Wijzigt training inclusief optionele training_aanwezigen-koppeling. */
    public function update(
        int $id,
        ?int $training_aanwezigen_id,
        string $start,
        string $end,
        string $titel,
        string $date,
        ?string $description,
        string $status
    ): bool {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare(
                "UPDATE trainingen
                 SET training_aanwezigen_id = :training_aanwezigen_id,
                     start = :start,
                     end = :end,
                     titel = :titel,
                     date = :date,
                     description = :description,
                     status = :status
                 WHERE id = :id"
            );

            $stmt->execute([
                'id'                     => $id,
                'training_aanwezigen_id' => $training_aanwezigen_id,
                'start'                  => $start,
                'end'                    => $end,
                'titel'                  => $titel,
                'date'                   => $date,
                'description'            => $description,
                'status'                 => $status
            ]);

            $this->pdo->commit();
            return true;

        } catch (PDOException $e) {

            $this->pdo->rollBack();
            error_log("Trainingen::update error: " . $e->getMessage());

            return false;
        }
    }

    /** Verwijdert training. */
    public function delete(int $id): bool
    {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare(
                "DELETE FROM trainingen WHERE id = :id"
            );

            $stmt->execute(['id' => $id]);

            $this->pdo->commit();
            return true;

        } catch (PDOException $e) {

            $this->pdo->rollBack();
            error_log("Trainingen::delete error: " . $e->getMessage());

            return false;
        }
    }

    /**
     * True als er op deze datum al een training is die met [start, end] overlapt.
     */
    public function trainingControle(
        string $date,
        string $start,
        string $end
    ): bool {

        try {
            $stmt = $this->pdo->prepare(
                "SELECT COUNT(*) FROM trainingen
                 WHERE date = :date
                 AND (
                        (:start < `end`)
                        AND
                        (:end > `start`)
                     )"
            );

            $stmt->execute([
                'date'  => $date,
                'start' => $start,
                'end'   => $end
            ]);

            return $stmt->fetchColumn() > 0;

        } catch (PDOException $e) {

            error_log("Trainingen::trainingControle error: " . $e->getMessage());
            return false;
        }
    }
}
