<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class User
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /** Nieuwe gebruiker met gehasht wachtwoord. */
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

            error_log("User::create error: " . $e->getMessage());

            return false;
        }
    }

    /** Alle gebruikers. */
    public function readAll(): array
    {
        try {
            $stmt = $this->pdo->query("SELECT * FROM user");
            return $stmt->fetchAll();
        } catch (PDOException $e) {

            error_log("User::readAll error: " . $e->getMessage());
            return [];
        }
    }

    /** Eén gebruiker op id. */
    public function read(int $id): array|false
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM user WHERE id = :id");
            $stmt->execute(['id' => $id]);

            return $stmt->fetch();

        } catch (PDOException $e) {

            error_log("User::read error: " . $e->getMessage());
            return false;
        }
    }

    /** Wijzigt e-mail, rol en lidnummer (geen wachtwoord). */
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

            error_log("User::update error: " . $e->getMessage());

            return false;
        }
    }

    /** Verwijdert gebruiker. */
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

            error_log("User::delete error: " . $e->getMessage());

            return false;
        }
    }

    /**
     * Controleert e-mail/lidnummer + wachtwoord, zet 2FA-sessievariabelen en mailt een code.
     * echte login na verify.php.
     */
    public function login(string $identifier, string $password): bool
    {
        try {
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

            $code = random_int(1000, 9999);

            $_SESSION['2fa_user_id'] = $user['id'];
            $_SESSION['2fa_role']    = $user['userrol'];
            $_SESSION['2fa_code']    = $code;
            $_SESSION['2fa_expires'] = time() + 300;

            $this->send2FA($user['email'], $code);

            return true;

        } catch (PDOException $e) {
            error_log("User::login error: " . $e->getMessage());
            return false;
        }
    }

    /** Wis sessie en destroy. */
    public function logout(): void
    {
        $_SESSION = [];
        session_destroy();
    }

    /** True als user_id in sessie staat (na geslaagde 2FA). */
    public function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']);
    }

    /** Kernvelden uit sessie voor de ingelogde gebruiker. */
    public function currentUser(): ?array
    {
        if (!$this->isLoggedIn()) {
            return null;
        }

        return [
            'id'        => $_SESSION['user_id']   ?? null,
            'email'     => $_SESSION['email']     ?? null,
            'lidnummer' => $_SESSION['lidnummer'] ?? null,
            'role'      => $_SESSION['role']      ?? null
        ];
    }

    /** Stuurt de 2FA-code per e-mail (SMTP). */
    private function send2FA(string $email, int $code): void
    {
        require_once __DIR__ . '/../vendor/autoload.php';

        $mail = new PHPMailer(true);

        try {

            $mail->isSMTP();
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer'       => false,
                    'verify_peer_name'  => false,
                    'allow_self_signed' => true,
                ],
            ];
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'jeremyversteeg37@gmail.com';
            $mail->Password   = 'novz figa vdxd kzjc';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('jeremyversteeg37@gmail.com', 'GoalGetter');
            $mail->addAddress($email);

            $mail->Subject = 'Jouw 2FA Code';
            $mail->Body    = "Jouw verificatiecode is: $code";

            $mail->send();

        } catch (Exception $e) {
            echo "Mailer Error: " . $mail->ErrorInfo;
            exit;
        }
    }
}
