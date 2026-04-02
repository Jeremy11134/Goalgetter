<?php

class Statistieken
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /* ===============================
       CREATE
    =============================== */

    public function create(int $goals, int $win, int $draw, int $loses): bool
    {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare(
                "INSERT INTO statistieken (goals, win, draw, loses)
                 VALUES (:goals, :win, :draw, :loses)"
            );

            $stmt->execute([
                'goals' => $goals,
                'win'   => $win,
                'draw'  => $draw,
                'loses' => $loses
            ]);

            $this->pdo->commit();
            return true;

        } catch (PDOException $e) {

            $this->pdo->rollBack();
            error_log("Statistieken::create error: " . $e->getMessage());

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
                "SELECT * FROM statistieken ORDER BY id DESC"
            );

            return $stmt->fetchAll();

        } catch (PDOException $e) {

            error_log("Statistieken::readAll error: " . $e->getMessage());
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
                "SELECT * FROM statistieken WHERE id = :id"
            );

            $stmt->execute(['id' => $id]);

            return $stmt->fetch();

        } catch (PDOException $e) {

            error_log("Statistieken::read error: " . $e->getMessage());
            return false;
        }
    }

    /* ===============================
       UPDATE
    =============================== */

    public function update(
        int $id,
        int $goals,
        int $win,
        int $draw,
        int $loses
    ): bool {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare(
                "UPDATE statistieken
                 SET goals = :goals,
                     win   = :win,
                     draw  = :draw,
                     loses = :loses
                 WHERE id = :id"
            );

            $stmt->execute([
                'id'    => $id,
                'goals' => $goals,
                'win'   => $win,
                'draw'  => $draw,
                'loses' => $loses
            ]);

            $this->pdo->commit();
            return true;

        } catch (PDOException $e) {

            $this->pdo->rollBack();
            error_log("Statistieken::update error: " . $e->getMessage());

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
                "DELETE FROM statistieken WHERE id = :id"
            );

            $stmt->execute(['id' => $id]);

            $this->pdo->commit();
            return true;

        } catch (PDOException $e) {

            $this->pdo->rollBack();
            error_log("Statistieken::delete error: " . $e->getMessage());

            return false;
        }
    }
}
