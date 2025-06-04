<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../HEW_login/login.php');
    exit;
}

$customerId = $_SESSION['user_id'];

$databasePath = "C:/xampp/htdocs/database/AccessDB.accdb";
if (!file_exists($databasePath)) {
    die('指定されたデータベースファイルが存在しません。');
}
$dsn = "odbc:Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=$databasePath;";

try {
    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 配送先削除処理
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_address_id'])) {
        $deleteAddressId = $_POST['delete_address_id'];
        $deleteSql = "DELETE FROM delivery_address WHERE delivery_address_id = :delivery_address_id AND customer_id = :customer_id";
        $deleteStmt = $pdo->prepare($deleteSql);
        $deleteStmt->execute([':delivery_address_id' => $deleteAddressId, ':customer_id' => $customerId]);
    }

    // 配送先情報を取得
    $sql = "
        SELECT d.delivery_address_id, p.prefecture_name, c.city_name, d.street, d.building_name,
                d.phone_number, d.delivery_name
        FROM ((delivery_address AS d
        INNER JOIN prefecture AS p ON d.prefecture_id = p.prefecture_id)
        INNER JOIN cities AS c ON d.city_id = c.city_id)
        WHERE d.customer_id = :customer_id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':customer_id' => $customerId]);
    $addresses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 文字コード変換
    foreach ($addresses as &$address) {
        $address['prefecture_name'] = mb_convert_encoding($address['prefecture_name'], "UTF-8", "SJIS");
        $address['city_name'] = mb_convert_encoding($address['city_name'], "UTF-8", "SJIS");
        $address['street'] = mb_convert_encoding($address['street'], "UTF-8", "SJIS");
        if (!empty($address['building_name'])) {
            $address['building_name'] = mb_convert_encoding($address['building_name'], "UTF-8", "SJIS");
        }
        $address['phone_number'] = $address['phone_number'] ? mb_convert_encoding($address['phone_number'], "UTF-8", "SJIS") : 'なし';
        $address['delivery_name'] = mb_convert_encoding($address['delivery_name'], "UTF-8", "SJIS");
    }
    unset($address);

} catch (PDOException $e) {
    die("データベースエラー: " . $e->getMessage());
}

$previousPage = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'mypage.php';

if (strpos($previousPage, 'mypage.php') !== false) {
    $backUrl = '../HEW_mypage/mypage.php';

} elseif (strpos($previousPage, 'shipping-method.php') !== false) {
    $backUrl = '../HEW_shipping-method/shipping-method.php';

} elseif (strpos($previousPage, 'shipping-add.php') !== false) {
    $backUrl = '../HEW_shipping-method/shipping-method.php';

} else {
    $backUrl = '../HEW_mypage/mypage.php'; // バグ回避
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>登録済み配送先一覧 | げみねっと</title>
    <link rel="stylesheet" href="../../assets/scripts/reset.css"/>
    <link rel="stylesheet" href="shipping-list.css">
</head>

<body>

<?php include("../../assets/HEW_menu/menu.php"); ?>

<div class="container">
    <div class="back-button-area">
        <a class="back-button-link" href="<?php echo htmlspecialchars($backUrl); ?>"><button class="back-button">＜ 戻る</button></a>
    </div>
    <div class="container-area">
        <h1>登録済み配送先一覧</h1>

        <?php if (!empty($addresses)): ?>
            <?php foreach ($addresses as $address): ?>
                <div class="card-area">
                    <button class="edit-button">
                        <a href="../HEW_shipping-edit/shipping-edit.php?delivery_address_id=<?= htmlspecialchars($address['delivery_address_id']); ?>">
                            編集
                        </a>
                    </button>

                    <div class="address">
                        <label for="">住所</label>
                        <p>
                            <span><?= htmlspecialchars($address['prefecture_name']); ?></span>
                            <span><?= htmlspecialchars($address['city_name']); ?></span>
                            <span><?= htmlspecialchars($address['street']); ?></span>
                            <span><?= htmlspecialchars($address['building_name']); ?></span>
                        </p>
                    </div>

                    <div class="name">
                        <label for="">受取人</label>
                        <p><?= htmlspecialchars($address['delivery_name']); ?></p>
                    </div>

                    <div class="phone">
                        <label for="">電話番号</label>
                        <p><?= htmlspecialchars($address['phone_number']); ?></p>
                    </div>

                    <form method="POST" action="">
                        <input type="hidden" name="delete_address_id" value="<?= htmlspecialchars($address['delivery_address_id']); ?>">
                        <button type="submit" class="delete-button">削除</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>登録済みの配送先がありません。</p>
        <?php endif; ?>
    </div>

    <button class="add-button" onclick="window.location.href='../HEW_shipping-add/shipping-add.php';">住所を追加</button>
</div>
<?php include("../../assets/HEW_footer/footer.php"); ?>
</body>
</html>
