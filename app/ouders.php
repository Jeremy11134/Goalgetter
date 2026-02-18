<?php

class Ouders
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /* ===============================
       CREATE
    =============================== */

    public function create(int $user_id, int $person_id): bool
    {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare(
                "INSERT INTO ouders (user_id, person_id)
                 VALUES (:user_id, :person_id)"
            );

            $stmt->execute([
                'user_id'   => $user_id,
                'person_id' => $person_id
            ]);

            $this->pdo->commit();
            return true;

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Ouders::create error: " . $e->getMessage());
            return false;
        }
    }

    /* ===============================
       READ ALL
    =============================== */

    public function readAll(): array
    {
        try {
            $stmt = $this->pdo->query("SELECT * FROM ouders ORDER BY id DESC");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Ouders::readAll error: " . $e->getMessage());
            return [];
        }
    }

    /* ===============================
       READ ONE
    =============================== */

    public function read(int $id): array|false
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM ouders WHERE id = :id");
            $stmt->execute(['id' => $id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Ouders::read error: " . $e->getMessage());
            return false;
        }
    }

    /* ===============================
       UPDATE
    =============================== */

    public function update(int $id, int $user_id, int $person_id): bool
    {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare(
                "UPDATE ouders
                 SET user_id = :user_id,
                     person_id = :person_id
                 WHERE id = :id"
            );

            $stmt->execute([
                'id'        => $id,
                'user_id'   => $user_id,
                'person_id' => $person_id
            ]);

            $this->pdo->commit();
            return true;

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Ouders::update error: " . $e->getMessage());
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
                "DELETE FROM ouders WHERE id = :id"
            );

            $stmt->execute(['id' => $id]);

            $this->pdo->commit();
            return true;

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Ouders::delete error: " . $e->getMessage());
            return false;
        }
    }

    /* ===============================
       REGISTER OUDER
    =============================== */

    public function registerouder(
        string $voornaam,
        ?string $tussenvoegsels,
        string $achternaam,
        string $email,
        string $password,
        string $lidnummer
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
                "INSERT INTO user (email, userrol, password, lidnummer)
                 VALUES (:email, :userrol, :password, :lidnummer)"
            );

            $stmtUser->execute([
                'email'     => $email,
                'userrol'   => 'ouder',
                'password'  => password_hash($password, PASSWORD_DEFAULT),
                'lidnummer' => $lidnummer
            ]);

            $user_id = $this->pdo->lastInsertId();

            /* Ouder */
            $stmtOuder = $this->pdo->prepare(
                "INSERT INTO ouders (user_id, person_id)
                 VALUES (:user_id, :person_id)"
            );

            $stmtOuder->execute([
                'user_id'   => $user_id,
                'person_id' => $person_id
            ]);

            $this->pdo->commit();
            return true;

        } catch (PDOException $e) {

            $this->pdo->rollBack();
            error_log("Ouders::registerouder error: " . $e->getMessage());

            return false;
        }
    }
}
