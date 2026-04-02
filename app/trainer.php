<?php

class Trainer
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /** Nieuwe trainer (user + person + club). */
    public function create(int $user_id, int $person_id, int $club_id): bool
    {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare(
                "INSERT INTO trainer (user_id, person_id, club_id)
                 VALUES (:user_id, :person_id, :club_id)"
            );

            $stmt->execute([
                'user_id'   => $user_id,
                'person_id' => $person_id,
                'club_id'   => $club_id
            ]);

            $this->pdo->commit();
            return true;

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Trainer::create error: " . $e->getMessage());
            return false;
        }
    }

    /** Alle trainers. */
    public function readAll(): array
    {
        try {
            $stmt = $this->pdo->query("SELECT * FROM trainer ORDER BY id DESC");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Trainer::readAll error: " . $e->getMessage());
            return [];
        }
    }

    /** Eén trainer op id. */
    public function read(int $id): array|false
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM trainer WHERE id = :id");
            $stmt->execute(['id' => $id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Trainer::read error: " . $e->getMessage());
            return false;
        }
    }

    /** Wijzigt koppelingen. */
    public function update(
        int $id,
        int $user_id,
        int $person_id,
        int $club_id
    ): bool {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare(
                "UPDATE trainer
                 SET user_id = :user_id,
                     person_id = :person_id,
                     club_id = :club_id
                 WHERE id = :id"
            );

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
            error_log("Trainer::update error: " . $e->getMessage());
            return false;
        }
    }

    /** Verwijdert trainerrecord. */
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
            error_log("Trainer::delete error: " . $e->getMessage());
            return false;
        }
    }

    /** Registratie persoon + user (trainer) + trainer bij club. */
    public function registertrainer(
        string $voornaam,
        ?string $tussenvoegsels,
        string $achternaam,
        string $email,
        string $password,
        string $lidnummer,
        int $club_id
    ): bool {

        try {
            $this->pdo->beginTransaction();

            $stmtPerson = $this->pdo->prepare(
                "INSERT INTO person (voornaam, tussenvoegsels, achternaam)
                 VALUES (:voornaam, :tussenvoegsels, :achternaam)"
            );

            $stmtPerson->execute([
                'voornaam'       => $voornaam,
                'tussenvoegsels' => $tussenvoegsels ?? '',
                'achternaam'     => $achternaam
            ]);

            $person_id = $this->pdo->lastInsertId();

            $stmtUser = $this->pdo->prepare(
                "INSERT INTO `user` (email, userrol, password, lidnummer)
                 VALUES (:email, :userrol, :password, :lidnummer)"
            );

            $stmtUser->execute([
                'email'     => $email,
                'userrol'   => 'trainer',
                'password'  => password_hash($password, PASSWORD_DEFAULT),
                'lidnummer' => $lidnummer
            ]);

            $user_id = $this->pdo->lastInsertId();

            $stmtTrainer = $this->pdo->prepare(
                "INSERT INTO trainer (user_id, person_id, club_id)
                 VALUES (:user_id, :person_id, :club_id)"
            );

            $stmtTrainer->execute([
                'user_id'   => $user_id,
                'person_id' => $person_id,
                'club_id'   => $club_id
            ]);

            $this->pdo->commit();
            return true;

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Trainer::registertrainer error: " . $e->getMessage());
            return false;
        }
    }

    /** Training aanmaken + spelers in training_aanwezigen (sessie moet trainer zijn). */
    public function createTrainingMetSpelers(
        string $start,
        string $end,
        string $titel,
        string $date,
        ?string $description,
        string $status,
        array $speler_ids
    ): bool {

        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'trainer') {
            error_log("Unauthorized training creation attempt.");
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare(
                "INSERT INTO trainingen
                 (start, end, titel, date, description, status)
                 VALUES (:start, :end, :titel, :date, :description, :status)"
            );

            $stmt->execute([
                'start'       => $start,
                'end'         => $end,
                'titel'       => $titel,
                'date'        => $date,
                'description' => $description,
                'status'      => $status
            ]);

            $training_id = $this->pdo->lastInsertId();

            $stmtAanwezig = $this->pdo->prepare(
                "INSERT INTO training_aanwezigen
                 (speler_id, training_id, status)
                 VALUES (:speler_id, :training_id, :status)"
            );

            foreach ($speler_ids as $speler_id) {
                $stmtAanwezig->execute([
                    'speler_id'  => $speler_id,
                    'training_id'=> $training_id,
                    'status'     => 'aanwezig'
                ]);
            }

            $this->pdo->commit();
            return true;

        } catch (Throwable $e) {
            $this->pdo->rollBack();
            error_log("Trainer::createTrainingMetSpelers error: " . $e->getMessage());
            return false;
        }
    }

    /** Wedstrijd + spelers in wedstrijd_aanwezigen (sessie moet trainer zijn). */
    public function createWedstrijdMetSpelers(
        int $club_id,
        string $start,
        string $end,
        string $titel,
        string $date,
        ?string $description,
        string $status,
        array $speler_ids
    ): bool {

        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'trainer') {
            error_log("Unauthorized wedstrijd creation attempt.");
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare(
                "INSERT INTO wedstrijden
                 (club_id, start, end, titel, date, description, status)
                 VALUES (:club_id, :start, :end, :titel, :date, :description, :status)"
            );

            $stmt->execute([
                'club_id'     => $club_id,
                'start'       => $start,
                'end'         => $end,
                'titel'       => $titel,
                'date'        => $date,
                'description' => $description,
                'status'      => $status
            ]);

            $wedstrijd_id = $this->pdo->lastInsertId();

            $stmtAanwezig = $this->pdo->prepare(
                "INSERT INTO wedstrijd_aanwezigen
                 (speler_id, wedstrijd_id, status)
                 VALUES (:speler_id, :wedstrijd_id, :status)"
            );

            foreach ($speler_ids as $speler_id) {
                $stmtAanwezig->execute([
                    'speler_id'   => $speler_id,
                    'wedstrijd_id'=> $wedstrijd_id,
                    'status'      => 'aanwezig'
                ]);
            }

            $this->pdo->commit();
            return true;

        } catch (Throwable $e) {
            $this->pdo->rollBack();
            error_log("Trainer::createWedstrijdMetSpelers error: " . $e->getMessage());
            return false;
        }
    }
}
