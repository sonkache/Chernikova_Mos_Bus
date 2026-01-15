<?php
session_start();
function isAuth(): bool {
  return isset($_SESSION['user_id']);
}
function requireAuth(): void {
  if (!isAuth()) {
    header("Location: /auth/login.php");
    exit;
  }
}
