<?php

class Speler
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /* CREATE */
    public function create(
        int $user_id,
        int $person_id,
        int $club_id,
        int $statistieken_id
    ): bool {
        try {
            $this->pdo->beginTransaction();

            $sql = "INSERT INTO speler (user_id, person_id, club_id, statistieken_id)
                    VALUES (:user_id, :person_id, :club_id, :statistieken_id)";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'user_id'          => $user_id,
                'person_id'        => $person_id,
                'club_id'          => $club_id,
                'statistieken_id'  => $statistieken_id
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
        $stmt = $this->pdo->query("SELECT * FROM speler ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    /* READ ONE */
    public function read(int $id): array|false
    {
        $stmt = $this->pdo->prepare("SELECT * FROM speler WHERE id = :id");
        $stmt->execute(['id' => $id]);

        return $stmt->fetch();
    }

    /* UPDATE */
    public function update(
        int $id,
        int $user_id,
        int $person_id,
        int $club_id,
        int $statistieken_id
    ): bool {
        try {
            $this->pdo->beginTransaction();

            $sql = "UPDATE speler
                    SET user_id = :user_id,
                        person_id = :person_id,
                        club_id = :club_id,
                        statistieken_id = :statistieken_id
                    WHERE id = :id";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'id'               => $id,
                'user_id'          => $user_id,
                'person_id'        => $person_id,
                'club_id'          => $club_id,
                'statistieken_id'  => $statistieken_id
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
                "DELETE FROM speler WHERE id = :id"
            );
            $stmt->execute(['id' => $id]);

            $this->pdo->commit();
            return true;

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

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
                'userrol'   => 'speler',
                'password'  => $hashedPassword,
                'lidnummer' => $lidnummer
            ]);

            $user_id = $this->pdo->lastInsertId();



            $stmtStats = $this->pdo->prepare(
                "INSERT INTO statistieken (goals, win, draw, loses)
                VALUES (0, 0, 0, 0)"
            );

            $stmtStats->execute();

            $statistieken_id = $this->pdo->lastInsertId();


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
            die("Database error: " . $e->getMessage());
        }
    }

}
