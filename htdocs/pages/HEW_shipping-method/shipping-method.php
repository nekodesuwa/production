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

    // 配送先情報の取得
    $sql = "
        SELECT d.delivery_address_id, d.prefecture_id, p.prefecture_name, d.city_id, c.city_name, d.street, d.building_name, d.postal_code
        FROM ((delivery_address AS d
        INNER JOIN prefecture AS p ON d.prefecture_id = p.prefecture_id)
        INNER JOIN cities AS c ON d.city_id = c.city_id)
        WHERE d.customer_id = :customer_id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':customer_id' => $customerId]);
    $addresses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 文字化け防止
    foreach ($addresses as &$address) {
        $address['prefecture_name'] = mb_convert_encoding($address['prefecture_name'], "UTF-8", "SJIS");
        $address['city_name'] = mb_convert_encoding($address['city_name'], "UTF-8", "SJIS");
        $address['street'] = mb_convert_encoding($address['street'], "UTF-8", "SJIS");
        if (!empty($address['building_name'])) {
            $address['building_name'] = mb_convert_encoding($address['building_name'], "UTF-8", "SJIS");
        }
        $address['postal_code'] = mb_convert_encoding($address['postal_code'], "UTF-8", "SJIS");
    }
    unset($address);

} catch (PDOException $e) {
    die("データベースエラー: " . $e->getMessage());
}

// POSTデータをセッションに保存
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_address'])) {
    $_SESSION['selected_address'] = $_POST['selected_address'];
    header('Location: ../HEW_payment-method/payment-method.php');
    exit;
}

?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>配送方法の選択</title>
    <link rel="stylesheet" href="../../assets/scripts/reset.css">
    <link rel="stylesheet" href="shipping-method.css">
    <script src="shipping-method.js" defer></script>
</head>
<body>

<?php include("../../assets/HEW_menu/menu.php"); ?>

<div class="container">
    <div class="back-button-area">
        <a class="back-button-link" href="../HEW_cart/cart.php"><button class="back-button">＜ 戻る</button></a>
    </div>
    <div class="container-area">
        <h1>配送先の選択</h1>
        <div class="registered-addresses">
            <h2>登録済み配送先</h2>
            <form method="POST" action="" id="shipping-form">
                <?php if (!empty($addresses)): ?>
                    <?php foreach ($addresses as $address): ?>
                        <div class="address-item">
                            <input type="radio" id="address<?php echo htmlspecialchars($address['delivery_address_id']); ?>"
                                name="selected_address"
                                value="<?php echo htmlspecialchars($address['delivery_address_id']); ?>" required>
                            <label for="address<?php echo htmlspecialchars($address['delivery_address_id']); ?>">
                                〒<?php echo htmlspecialchars($address['postal_code']); ?><br>
                                <?php echo htmlspecialchars($address['prefecture_name'] . ' ' . $address['city_name']); ?><br>
                                <?php echo htmlspecialchars($address['street'] . ' ' . ($address['building_name'] ?? '')); ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>登録済みの配送先がありません。</p>
                <?php endif; ?>
            </div>
            <div class="add-new-address">
                <button type="button" class="add-address" onclick="window.location.href='../HEW_shipping-list/shipping-list.php';">配送先を追加</button>
                <button type="submit" id="proceed-button" class="disabled-button" disabled>決済方法の選択へ進む</button>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const proceedButton = document.getElementById("proceed-button");
        const radioButtons = document.querySelectorAll("input[name='selected_address']");

        // 初期状態でボタンを無効化
        proceedButton.disabled = true;
        proceedButton.classList.add("disabled-button");

        // ラジオボタン選択時にボタンを有効化
        radioButtons.forEach(function (radio) {
            radio.addEventListener("change", function () {
                proceedButton.disabled = false;
                proceedButton.classList.remove("disabled-button");
                proceedButton.classList.add("active-button");
            });
        });
    });
</script>
<?php include("../../assets/HEW_footer/footer.php"); ?>
</body>
</html>
