<?php
function check_session_timeout() {
    $timeout = 30 * 60; // 30 minutes
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
        session_unset();
        session_destroy();
        header('Location: ../login.html?timeout=1');
        exit();
    }
    $_SESSION['last_activity'] = time();
}
