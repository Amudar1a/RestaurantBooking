<?php

class Admin {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function authenticate($username, $password) {
        $stmt = $this->db->prepare("SELECT * FROM admin_users WHERE username = :username");
        $stmt->execute([':username' => $username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            return $admin;
        }
        return false;
    }

    public function isLoggedIn() {
        return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
    }

    public function login($admin) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['admin_email'] = $admin['email'];
    }

    public function logout() {
        session_destroy();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT id, username, email, created_at FROM admin_users WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }
}
