<?php

class Speler
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
        int $user_id,
        int $person_id,
        int $club_id,
        int $statistieken_id
    ): bool {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare(
                "INSERT INTO speler (user_id, person_id, club_id, statistieken_id)
                 VALUES (:user_id, :person_id, :club_id, :statistieken_id)"
            );

            $stmt->execute([
                'user_id'         => $user_id,
                'person_id'       => $person_id,
                'club_id'         => $club_id,
                'statistieken_id' => $statistieken_id
            ]);

            $this->pdo->commit();
            return true;

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Speler::create error: " . $e->getMessage());
            return false;
        }
    }

    /* ===============================
       READ ALL
    =============================== */

    public function readAll(): array
    {
        try {
            $stmt = $this->pdo->query("SELECT * FROM speler ORDER BY id DESC");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Speler::readAll error: " . $e->getMessage());
            return [];
        }
    }

    /* ===============================
       READ ONE
    =============================== */

    public function read(int $id): array|false
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM speler WHERE id = :id");
            $stmt->execute(['id' => $id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Speler::read error: " . $e->getMessage());
            return false;
        }
    }

    /* ===============================
       UPDATE
    =============================== */

    public function update(
        int $id,
        int $user_id,
        int $person_id,
        int $club_id,
        int $statistieken_id
    ): bool {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare(
                "UPDATE speler
                 SET user_id = :user_id,
                     person_id = :person_id,
                     club_id = :club_id,
                     statistieken_id = :statistieken_id
                 WHERE id = :id"
            );

            $stmt->execute([
                'id'              => $id,
                'user_id'         => $user_id,
                'person_id'       => $person_id,
                'club_id'         => $club_id,
                'statistieken_id' => $statistieken_id
            ]);

            $this->pdo->commit();
            return true;

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Speler::update error: " . $e->getMessage());
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
                "DELETE FROM speler WHERE id = :id"
            );

            $stmt->execute(['id' => $id]);

            $this->pdo->commit();
            return true;

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Speler::delete error: " . $e->getMessage());
            return false;
        }
    }

    /* ===============================
       REGISTER SPELER
    =============================== */

    public function registerspeler(
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

            /* Person */
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

            /* User */
            $stmtUser = $this->pdo->prepare(
                "INSERT INTO `user` (email, userrol, password, lidnummer)
                 VALUES (:email, :userrol, :password, :lidnummer)"
            );

            $stmtUser->execute([
                'email'     => $email,
                'userrol'   => 'speler',
                'password'  => password_hash($password, PASSWORD_DEFAULT),
                'lidnummer' => $lidnummer
            ]);

            $user_id = $this->pdo->lastInsertId();

            /* Stats */
            $stmtStats = $this->pdo->prepare(
                "INSERT INTO statistieken (goals, win, draw, loses)
                 VALUES (0, 0, 0, 0)"
            );

            $stmtStats->execute();
            $statistieken_id = $this->pdo->lastInsertId();

            /* Speler */
            $stmtSpeler = $this->pdo->prepare(
                "INSERT INTO speler (user_id, person_id, club_id, statistieken_id)
                 VALUES (:user_id, :person_id, :club_id, :statistieken_id)"
            );

            $stmtSpeler->execute([
                'user_id'         => $user_id,
                'person_id'       => $person_id,
                'club_id'         => $club_id,
                'statistieken_id' => $statistieken_id
            ]);

            $this->pdo->commit();
            return true;

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Speler::registerspeler error: " . $e->getMessage());
            return false;
        }
    }

    /* ===============================
       GET STATISTIEKEN
    =============================== */

    public function getStatistieken(int $speler_id): array|false
    {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT statistieken_id
                 FROM speler
                 WHERE id = :id
                 LIMIT 1"
            );

            $stmt->execute(['id' => $speler_id]);
            $result = $stmt->fetch();

            if (!$result) {
                error_log("Speler::getStatistieken - speler niet gevonden: {$speler_id}");
                return false;
            }

            require_once 'Statistieken.php';
            $statistieken = new Statistieken($this->pdo);

            return $statistieken->read((int)$result['statistieken_id']);

        } catch (PDOException $e) {
            error_log("Speler::getStatistieken error: " . $e->getMessage());
            return false;
        }
    }

    /* ===============================
       MELD AANWEZIGHEID
    =============================== */

    public function meldAanwezigheid(
        string $type,
        int $event_id,
        string $status
    ): bool {

        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'speler') {
            error_log("Unauthorized aanwezigheidsactie.");
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare(
                "SELECT id FROM speler WHERE user_id = :user_id LIMIT 1"
            );

            $stmt->execute([
                'user_id' => $_SESSION['user_id']
            ]);

            $speler = $stmt->fetch();

            if (!$speler) {
                error_log("Speler::meldAanwezigheid - speler niet gevonden.");
                throw new Exception("Speler niet gevonden");
            }

            $speler_id = $speler['id'];

            if ($type === 'training') {
                $table = 'training_aanwezigen';
                $column = 'training_id';
            } elseif ($type === 'wedstrijd') {
                $table = 'wedstrijd_aanwezigen';
                $column = 'wedstrijd_id';
            } else {
                error_log("Speler::meldAanwezigheid - ongeldig type: {$type}");
                throw new Exception("Ongeldig type");
            }

            $stmt = $this->pdo->prepare(
                "SELECT id FROM $table
                 WHERE speler_id = :speler_id
                 AND $column = :event_id
                 LIMIT 1"
            );

            $stmt->execute([
                'speler_id' => $speler_id,
                'event_id'  => $event_id
            ]);

            $exists = $stmt->fetch();

            if ($exists) {
                $stmt = $this->pdo->prepare(
                    "UPDATE $table
                     SET status = :status
                     WHERE speler_id = :speler_id
                     AND $column = :event_id"
                );
            } else {
                $stmt = $this->pdo->prepare(
                    "INSERT INTO $table
                     (speler_id, $column, status)
                     VALUES (:speler_id, :event_id, :status)"
                );
            }

            $stmt->execute([
                'speler_id' => $speler_id,
                'event_id'  => $event_id,
                'status'    => $status
            ]);

            $this->pdo->commit();
            return true;

        } catch (Throwable $e) {
            $this->pdo->rollBack();
            error_log("Speler::meldAanwezigheid error: " . $e->getMessage());
            return false;
        }
    }
}
