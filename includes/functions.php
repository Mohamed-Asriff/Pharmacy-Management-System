<?php
session_start();

function require_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../index.php?error=Please login");
        exit;
    }
}

function require_role($role) {
    require_login();
    if ($_SESSION['role'] !== $role) {
        die("Access denied. Requires $role role.");
    }
}

function require_roles($roles = []) {
    require_login();
    if (!in_array($_SESSION['role'], $roles)) {
        die("Access denied.");
    }
}
