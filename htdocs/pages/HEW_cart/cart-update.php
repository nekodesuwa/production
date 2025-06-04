<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "not_logged_in"]);
    exit;
}

if (!isset($_POST['cart_item_id']) || !ctype_digit($_POST['cart_item_id']) || !isset($_POST['quantity']) || !ctype_digit($_POST['quantity'])) {
    echo json_encode(["error" => "invalid_data"]);
    exit;
}

$cartItemId = (int)$_POST['cart_item_id'];
$quantity = (int)$_POST['quantity'];
$customerId = $_SESSION['user_id'];

$databasePath = "C:/xampp/htdocs/database/AccessDB.accdb";
if (!file_exists($databasePath)) {
    echo json_encode(["error" => "database_not_found"]);
    exit;
}

$dsn = "odbc:Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=$databasePath;";

try {
    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // カート内の商品が現在のユーザーのものか
    $sql = "SELECT cart_item_id FROM cart_item WHERE cart_item_id = :cart_item_id AND customer_id = :customer_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':cart_item_id' => $cartItemId, ':customer_id' => $customerId]);
    $cartItem = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cartItem) {
        echo json_encode(["error" => "not_found"]);
        exit;
    }

    // 数量を更新
    $sql = "UPDATE cart_item SET quantity = :quantity, updated_at = NOW() WHERE cart_item_id = :cart_item_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':quantity' => $quantity, ':cart_item_id' => $cartItemId]);

    echo json_encode(["success" => true, "new_quantity" => $quantity]);
} catch (PDOException $e) {
    echo json_encode(["error" => "database_error", "message" => $e->getMessage()]);
}
?>
