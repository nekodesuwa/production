<?php
session_start();

if (!isset($_SESSION['employee_id'])) {
    header('Location: employee_login.php'); // ログインページにリダイレクト
    exit;
}

// ログイン中の社員情報を取得
$employeeId = $_SESSION['employee_id'];
$role = $_SESSION['role'];
?>
