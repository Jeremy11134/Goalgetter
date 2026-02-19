<?php

class Wedstrijden
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
    string $start,
    string $end,
    string $titel,
    string $date,
    string $status
): bool {

    try {
        $this->pdo->beginTransaction();

        $stmt = $this->pdo->prepare(
            "INSERT INTO wedstrijden (start, end, titel, date, status)
             VALUES (:start, :end, :titel, :date, :status)"
        );

        $stmt->execute([
            'start'  => $start,
            'end'    => $end,
            'titel'  => $titel,
            'date'   => $date,
            'status' => $status
        ]);

        $this->pdo->commit();
        return true;

    } catch (PDOException $e) {
        $this->pdo->rollBack();
        error_log("Wedstrijden::create error: " . $e->getMessage());
        return false;
    }
}
    /* ===============================
       READ ALL
    =============================== */

    public function readAll(): array
    {
        try {
            $stmt = $this->pdo->query(
                "SELECT * FROM wedstrijden ORDER BY date DESC, start"
            );

            return $stmt->fetchAll();

        } catch (PDOException $e) {

            error_log("Wedstrijden::readAll error: " . $e->getMessage());
            return [];
        }
    }

    /* ===============================
       READ ONE
    =============================== */

    public function read(int $id): array|false
    {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT * FROM wedstrijden WHERE id = :id"
            );

            $stmt->execute(['id' => $id]);

            return $stmt->fetch();

        } catch (PDOException $e) {

            error_log("Wedstrijden::read error: " . $e->getMessage());
            return false;
        }
    }

    /* ===============================
       UPDATE
    =============================== */

public function update(
    int $id,
    string $start,
    string $end,
    string $titel,
    string $date,
    string $status
): bool {

    try {
        $this->pdo->beginTransaction();

        $stmt = $this->pdo->prepare(
            "UPDATE wedstrijden
             SET start = :start,
                 end = :end,
                 titel = :titel,
                 date = :date,
                 status = :status
             WHERE id = :id"
        );

        $stmt->execute([
            'id'     => $id,
            'start'  => $start,
            'end'    => $end,
            'titel'  => $titel,
            'date'   => $date,
            'status' => $status
        ]);

        $this->pdo->commit();
        return true;

    } catch (PDOException $e) {
        $this->pdo->rollBack();
        error_log("Wedstrijden::update error: " . $e->getMessage());
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
                "DELETE FROM wedstrijden WHERE id = :id"
            );

            $stmt->execute(['id' => $id]);

            $this->pdo->commit();
            return true;

        } catch (PDOException $e) {

            $this->pdo->rollBack();
            error_log("Wedstrijden::delete error: " . $e->getMessage());

            return false;
        }
    }
}
