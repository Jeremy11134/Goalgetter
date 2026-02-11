<?php

class User
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /* CREATE */
    public function create(
        string $email,
        string $userrol,
        string $password,
        string $lidnummer
    ): bool {
        try {
            $this->pdo->beginTransaction();

            $sql = "INSERT INTO user (email, userrol, password, lidnummer)
                    VALUES (:email, :userrol, :password, :lidnummer)";

            $stmt = $this->pdo->prepare($sql);

            $stmt->execute([
                'email'     => $email,
                'userrol'   => $userrol,
                'password'  => password_hash($password, PASSWORD_DEFAULT),
                'lidnummer' => $lidnummer
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
        $stmt = $this->pdo->query("SELECT * FROM user");
        return $stmt->fetchAll();
    }

    /* READ ONE */
    public function read(int $id): array|false
    {
        $stmt = $this->pdo->prepare("SELECT * FROM user WHERE id = :id");
        $stmt->execute(['id' => $id]);

        return $stmt->fetch();
    }

    /* UPDATE */
    public function update(
        int $id,
        string $email,
        string $userrol,
        string $lidnummer
    ): bool {
        try {
            $this->pdo->beginTransaction();

            $sql = "UPDATE user
                    SET email = :email,
                        userrol = :userrol,
                        lidnummer = :lidnummer
                    WHERE id = :id";

            $stmt = $this->pdo->prepare($sql);

            $stmt->execute([
                'id'        => $id,
                'email'     => $email,
                'userrol'   => $userrol,
                'lidnummer' => $lidnummer
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

            $stmt = $this->pdo->prepare("DELETE FROM user WHERE id = :id");
            $stmt->execute(['id' => $id]);

            $this->pdo->commit();
            return true;

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            return false;
        }
    }
}
