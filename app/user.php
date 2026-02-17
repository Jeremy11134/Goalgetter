<?php

class User
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        {
            $this->pdo = $pdo;
    

            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
        }
    }


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

  
    public function readAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM user");
        return $stmt->fetchAll();
    }


    public function read(int $id): array|false
    {
        $stmt = $this->pdo->prepare("SELECT * FROM user WHERE id = :id");
        $stmt->execute(['id' => $id]);

        return $stmt->fetch();
    }

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





    public function login(string $identifier, string $password): bool
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM `user`
             WHERE email = :email
             OR lidnummer = :lidnummer
             LIMIT 1"
        );
    
        $stmt->execute([
            'email'     => $identifier,
            'lidnummer' => $identifier
        ]);
    
        $user = $stmt->fetch();
    
        if (!$user) {
            return false;
        }
    
        if (!password_verify($password, $user['password'])) {
            return false;
        }
    
        session_regenerate_id(true);
    
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['email']     = $user['email'];
        $_SESSION['lidnummer'] = $user['lidnummer'];
        $_SESSION['role']      = $user['userrol'];
    
        return true;
    }


    public function logout(): void
    {
        $_SESSION = [];
        session_destroy();
    }


    public function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']);
    }



    public function currentUser(): array|null
    {
        if (!$this->isLoggedIn()) {
            return null;
        }

        return [
            'id'        => $_SESSION['user_id'],
            'email'     => $_SESSION['email'],
            'lidnummer' => $_SESSION['lidnummer'],
            'role'      => $_SESSION['role']
        ];
    }
        
}