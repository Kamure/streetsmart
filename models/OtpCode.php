<?php
class OtpCode {
    private $pdo;
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function generate($user_id) {
        $code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));

        $stmt = $this->pdo->prepare("INSERT INTO otp_codes (user_id, code, expires_at, used, created_at, updated_at) VALUES (?, ?, ?, 0, NOW(), NOW())");
        $stmt->execute([$user_id, $code, $expires]);

        return $code;
    }

    public function verify($user_id, $code) {
        $stmt = $this->pdo->prepare("SELECT * FROM otp_codes WHERE user_id = ? AND code = ? AND used = 0 AND expires_at > NOW()");
        $stmt->execute([$user_id, $code]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function markUsed($id) {
        $stmt = $this->pdo->prepare("UPDATE otp_codes SET used = 1 WHERE id = ?");
        $stmt->execute([$id]);
    }
}
?>