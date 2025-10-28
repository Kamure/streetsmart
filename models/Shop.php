<?php
class Shop {
    private $pdo;
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function create($user_id, $name, $category, $location, $logo_path) {
        $stmt = $this->pdo->prepare("INSERT INTO shops (user_id, name, category, location, logo_path) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$user_id, $name, $category, $location, $logo_path]);
    }
}
?>