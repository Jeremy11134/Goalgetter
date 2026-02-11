<?php

class Ouders
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /* =====================================================
       BESTAANDE CRUD (optioneel voorbeeld)
    ===================================================== */

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
            return false;
        }
    }

    /* =====================================================
       NIEUWE FUNCTIE:
       Maakt person + user + ouder in 1 transactie
    ===================================================== */

    public function createFullOuder(
        string $voornaam,
        ?string $tussenvoegsels,
        string $achternaam,
        string $email,
        string $password,
        string $lidnummer
    ): bool {

        try {
            $this->pdo->beginTransaction();

            /* 1️⃣ PERSON */

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


            /* 2️⃣ USER */

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmtUser = $this->pdo->prepare(
                "INSERT INTO user (email, userrol, password, lidnummer)
                 VALUES (:email, :userrol, :password, :lidnummer)"
            );

            $stmtUser->execute([
                'email'     => $email,
                'userrol'   => 'ouder',
                'password'  => $hashedPassword,
                'lidnummer' => $lidnummer
            ]);

            $user_id = $this->pdo->lastInsertId();


            /* 3️⃣ OUDER KOPPELING */

            $stmtOuder = $this->pdo->prepare(
                "INSERT INTO ouders (user_id, person_id)
                 VALUES (:user_id, :person_id)"
            );

            $stmtOuder->execute([
                'user_id'   => $user_id,
                'person_id' => $person_id
            ]);


            /* 4️⃣ COMMIT */

            $this->pdo->commit();
            return true;

} catch (PDOException $e) {
    $this->pdo->rollBack();
    die("Database error: " . $e->getMessage());
}
    }
}
