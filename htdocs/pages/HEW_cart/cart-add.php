<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "not_logged_in"]);
    exit;
}

if (!isset($_POST['product_id']) || !ctype_digit($_POST['product_id'])) {
    echo json_encode(["error" => "invalid_product_id"]);
    exit;
}

$productId = (int)$_POST['product_id'];
$customerId = $_SESSION['user_id'];
$quantity = isset($_POST['quantity']) && ctype_digit($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

$databasePath = "C:/xampp/htdocs/database/AccessDB.accdb";
if (!file_exists($databasePath)) {
    echo json_encode(["error" => "database_not_found"]);
    exit;
}

$dsn = "odbc:Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=$databasePath;";

try {
    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // カートに同じ商品があるか確認
    $sql = "SELECT cart_item_id, quantity FROM cart_item WHERE customer_id = :customer_id AND product_id = :product_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':customer_id' => $customerId, ':product_id' => $productId]);
    $cartItem = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cartItem) {
        // 既にカートにあるなら数量を更新
        $newQuantity = $cartItem['quantity'] + $quantity;
        $sql = "UPDATE cart_item SET quantity = :quantity, updated_at = NOW() WHERE cart_item_id = :cart_item_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':quantity' => $newQuantity, ':cart_item_id' => $cartItem['cart_item_id']]);
    } else {
        // 新規追加
        $sql = "INSERT INTO cart_item (customer_id, product_id, quantity, created_at, updated_at) 
                VALUES (:customer_id, :product_id, :quantity, NOW(), NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':customer_id' => $customerId,
            ':product_id' => $productId,
            ':quantity' => $quantity,
        ]);
    }

    echo json_encode(["success" => true]);
} catch (PDOException $e) {
    echo json_encode(["error" => "database_error", "message" => $e->getMessage()]);
}
?>
