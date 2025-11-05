<?php
class Product {
    private $pdo;
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function create($shop_id, $name, $description, $price, $stock, $image_path, $category) {
        $stmt = $this->pdo->prepare("INSERT INTO products (shop_id, name, description, price, stock, image_path, category, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
        return $stmt->execute([$shop_id, $name, $description, $price, $stock, $image_path, $category]);
    }
}
?>