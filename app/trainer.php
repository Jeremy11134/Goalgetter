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



            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmtUser = $this->pdo->prepare(
                "INSERT INTO `user` (email, userrol, password, lidnummer)
                VALUES (:email, :userrol, :password, :lidnummer)"
            );

            $stmtUser->execute([
                'email'     => $email,
                'userrol'   => 'trainer',
                'password'  => $hashedPassword,
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
            die("Database error: " . $e->getMessage());
        }
    }


public function createTrainingMetSpelers(
    string $start,
    string $end,
    string $titel,
    string $date,
    ?string $description,
    string $status,
    array $speler_ids   // ← geselecteerde spelers
): bool {

    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'trainer') {
        return false;
    }

    try {
        $this->pdo->beginTransaction();

        /* 1️⃣ Training aanmaken */
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

        $training_id = $this->pdo->lastInsertId();

        /* 2️⃣ Spelers koppelen */
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
        return false;
    }
}


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
        return false;
    }

    try {
        $this->pdo->beginTransaction();

        /* 1️⃣ Wedstrijd aanmaken */
        $stmt = $this->pdo->prepare(
            "INSERT INTO wedstrijden
            (club_id, start, end, titel, date, description, status)
            VALUES
            (:club_id, :start, :end, :titel, :date, :description, :status)"
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

        /* 2️⃣ Spelers koppelen aan wedstrijd */
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
        return false;
    }
}

}