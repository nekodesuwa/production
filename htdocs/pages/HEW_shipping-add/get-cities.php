<?php
header("Content-Type: application/json; charset=UTF-8");

$databasePath = "C:/xampp/htdocs/database/AccessDB.accdb";
if (!file_exists($databasePath)) {
    die(json_encode(["error" => "データベースファイルが存在しません。"]));
}
$dsn = "odbc:Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=$databasePath;";

try {
    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (!isset($_GET['prefecture_id'])) {
        die(json_encode(["error" => "都道府県IDが指定されていません。"]));
    }

    $stmt = $pdo->prepare("SELECT city_id, city_name FROM cities WHERE prefecture_id = ?");
    $stmt->execute([$_GET['prefecture_id']]);
    $cities = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($cities as &$city) {
        $city['city_name'] = mb_convert_encoding($city['city_name'], "UTF-8", "SJIS");
    }

    echo json_encode($cities, JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    echo json_encode(["error" => "データベースエラー: " . $e->getMessage()]);
}
?>
