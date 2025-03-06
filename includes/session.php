<?php
session_start();

function check_login() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../login.html');
        exit();
    }
}

function check_admin() {
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'manager') {
        header('Location: ../login.html');
        exit();
    }
}

function get_user_id() {
    return $_SESSION['user_id'] ?? null;
}

function get_user_role() {
    return $_SESSION['user_role'] ?? null;
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}
