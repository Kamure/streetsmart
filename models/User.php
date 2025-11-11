<?php
class User {
    private $pdo;
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function create($name, $email, $phone, $password, $role) {
        $stmt = $this->pdo->prepare("INSERT INTO users (name, email, phone, password, role) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([
            $name,
            $email,
            $phone,
            password_hash($password, PASSWORD_DEFAULT),
            $role
        ]);
    }
     public function findByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateProfile($id, $name, $email, $phone) {
    $stmt = $this->pdo->prepare("
        UPDATE users 
        SET name = ?, email = ?, phone = ?
        WHERE id = ?
    ");
    return $stmt->execute([$name, $email, $phone, $id]);
}

}
?>