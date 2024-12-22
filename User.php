<?php
class User {
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function emailExists($email) {
        $query = "SELECT id FROM users WHERE email = :email LIMIT 1";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function register($name, $email, $password) {
        try {
            if ($this->emailExists($email)) {
                return false;
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $query = "INSERT INTO users (name, email, password) VALUES (:name, :email, :password)";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashedPassword);

            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            error_log("Error during registration: " . $e->getMessage());
            return false;
        }
    }

    public function authenticateUser($email, $password) {
        $query = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }
}
?>
