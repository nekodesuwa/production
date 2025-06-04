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

    // クレジットカード情報の取得
    $sql = "SELECT card_id, card_number FROM card_list WHERE customer_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user_id' => $customerId]);
    $cards = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ポイント残高の取得
    $sql = "SELECT points FROM user_account WHERE user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user_id' => $customerId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $points = $user['points'] ?? 0;

    // カート情報の取得
    $sql = "SELECT ci.cart_item_id, ci.quantity, p.product_name, p.price_excluding_tax, p.image_url, p.product_id 
    FROM [cart_item] AS ci
    INNER JOIN [product] AS p ON ci.product_id = p.product_id
    WHERE ci.customer_id = :customer_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':customer_id' => $customerId]);
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // カート内商品の合計金額を計算
    $totalPrice = array_sum(array_map(function ($item) {
        return $item['price_excluding_tax'] * $item['quantity'];
    }, $cartItems));


} catch (PDOException $e) {
    die("データベースエラー: " . $e->getMessage());
}

// 選択された決済方法とポイント利用をセッションに保存
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payment_method'])) {
    $_SESSION['selected_payment_method'] = $_POST['payment_method'];
    $_SESSION['used_points'] = isset($_POST['use_point']) ? (int)$_POST['point_amount'] : 0;
    header('Location: ../HEW_order-check/order-check.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>決済方法の選択</title>
    <link rel="stylesheet" href="../../assets/scripts/reset.css">
    <link rel="stylesheet" href="payment-method.css">
    <script src="payment-method.js" defer></script>
</head>
<body>

<?php include("../../assets/HEW_menu/menu.php"); ?>

<div class="contents-area">
    <div class="back-button-area">
        <a class="back-button-link" href="../HEW_shipping-method/shipping-method.php"><button class="back-button">＜ 戻る</button></a>
    </div>

    <form method="POST" action="">
        <div class="payment-container">
            <h1>決済方法の選択</h1>

            <!-- クレジットカード決済 -->
            <div class="accordion-item">
                <input type="radio" name="payment_method" id="credit-card" value="credit_card" class="accordion-radio">
                <label for="credit-card" class="accordion-button">
                    <span class="radio-visible"></span> クレジットカード決済
                    <img class="paymentimg" src="../../assets/img/payment/card_5brand.png">
                </label>
                <div class="accordion-content">
                    <p>登録済みのクレジットカードを選択するか、新しいカードを登録してください。</p>
                    <?php if (!empty($cards)): ?>
                        <?php foreach ($cards as $card): ?>
                            <input type="radio" name="card" value="<?php echo htmlspecialchars($card['card_number']); ?>">
                            <label>****...<?php echo substr($card['card_number'], -4); ?></label><br>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>登録済みのクレジットカードはありません。</p>
                    <?php endif; ?>
                    <button type="button" onclick="location.href='../HEW_card-list/card-list.php'">新しいカードを登録</button>
                </div>
            </div>

            <!-- コンビニ決済 -->
            <div class="accordion-item">
                <input type="radio" name="payment_method" id="convenience-store" value="コンビニ決済" class="accordion-radio">
                <label for="convenience-store" class="accordion-button">
                    <span class="radio-visible"></span> コンビニ決済
                    <img class="paymentimg" src="../../assets/img/payment/conveni.png">
                </label>
                <div class="accordion-content">
                    <p>ご希望のコンビニを選択してください。</p>
                    <select name="convenience_store">
                        <option value="seveneleven">セブンイレブン</option>
                        <option value="familymart">ファミリーマート</option>
                        <option value="lawson-ministop">ローソン / ミニストップ</option>
                        <option value="seicomart">セイコーマート</option>
                    </select>
                </div>
            </div>

            <!-- 銀行振込 -->
            <div class="accordion-item">
                <input type="radio" name="payment_method" id="bank-transfer" value="銀行振込" class="accordion-radio">
                <label for="bank-transfer" class="accordion-button">
                    <span class="radio-visible"></span> 銀行振込
                </label>
                <div class="accordion-content">
                    <p>振込先情報は注文完了後に表示されます。</p>
                </div>
            </div>

            <!-- 代引き決済 -->
            <div class="accordion-item">
                <input type="radio" name="payment_method" id="cash-on-delivery" value="代引き決済" class="accordion-radio">
                <label for="cash-on-delivery" class="accordion-button">
                    <span class="radio-visible"></span> 代引き決済
                </label>
                <div class="accordion-content">
                    <p>商品到着時に配達員にお支払いください。</p>
                </div>
            </div>
        </div>

        <!-- ポイント利用 -->
        <div class="point-area">
            <p>ポイント残量: <span><?php echo number_format($points); ?></span> P</p>
            <input type="checkbox" name="use_point" id="use-point" class="point-radio" value="1">
            <label for="use-point" class="point-radio">ポイントを利用する</label>
            <input type="number" id="point-input" name="point_amount" class="point-textbox"
            disabled min="0" max="<?php echo min($points, $totalPrice); ?>">

        </div>

        <div class="next-button-area">
            <button type="submit" class="next-button">注文情報の確認へ進む</button>
        </div>
    </form>
</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const pointCheckbox = document.getElementById("use-point");
        const pointInput = document.getElementById("point-input");
        const nextButton = document.querySelector(".next-button");
        const paymentMethods = document.querySelectorAll('input[name="payment_method"]');

        // 初期状態でボタンを無効化
        nextButton.disabled = true;

        // 決済方法の選択状態をチェック
        function checkSelection() {
            let selected = false;
            paymentMethods.forEach(method => {
                if (method.checked) {
                    selected = true;
                }
            });
            nextButton.disabled = !selected;
        }

        // ラジオボタンが選択されたときにボタンを有効化
        paymentMethods.forEach(method => {
            method.addEventListener("change", checkSelection);
        });

        // ポイントチェックボックスの制御
        pointCheckbox.addEventListener("change", () => {
            pointInput.disabled = !pointCheckbox.checked;
            if (!pointCheckbox.checked) {
                pointInput.value = "";
            }
        });
    });
</script>

<?php include("../../assets/HEW_footer/footer.php"); ?>
</body>
</html>
