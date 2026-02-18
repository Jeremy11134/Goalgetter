<?php

class WedstrijdAanwezigen
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
            error_log("WedstrijdAanwezigen::create error: " . $e->getMessage());

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
                "SELECT * FROM wedstrijd_aanwezigen ORDER BY id DESC"
            );
            return $stmt->fetchAll();

        } catch (PDOException $e) {

            error_log("WedstrijdAanwezigen::readAll error: " . $e->getMessage());
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
                "SELECT * FROM wedstrijd_aanwezigen WHERE id = :id"
            );
            $stmt->execute(['id' => $id]);

            return $stmt->fetch();

        } catch (PDOException $e) {

            error_log("WedstrijdAanwezigen::read error: " . $e->getMessage());
            return false;
        }
    }

    /* ===============================
       UPDATE
    =============================== */

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
            error_log("WedstrijdAanwezigen::update error: " . $e->getMessage());

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
                "DELETE FROM wedstrijd_aanwezigen WHERE id = :id"
            );
            $stmt->execute(['id' => $id]);

            $this->pdo->commit();
            return true;

        } catch (PDOException $e) {

            $this->pdo->rollBack();
            error_log("WedstrijdAanwezigen::delete error: " . $e->getMessage());

            return false;
        }
    }

    /* ===============================
       GET SPELERS VOOR WEDSTRIJD
    =============================== */

    public function getSpelersVoorWedstrijd(int $wedstrijd_id): array
    {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT 
                    wa.id,
                    p.voornaam,
                    p.tussenvoegsels,
                    p.achternaam,
                    wa.status
                FROM wedstrijd_aanwezigen wa
                JOIN speler s ON wa.speler_id = s.id
                JOIN person p ON s.person_id = p.id
                WHERE wa.wedstrijd_id = :wedstrijd_id
                ORDER BY p.achternaam ASC"
            );

            $stmt->execute([
                'wedstrijd_id' => $wedstrijd_id
            ]);

            return $stmt->fetchAll();

        } catch (PDOException $e) {

            error_log("WedstrijdAanwezigen::getSpelersVoorWedstrijd error: " . $e->getMessage());
            return [];
        }
    }
}
