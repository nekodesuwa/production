<?php
session_start();

// データベース接続
$databasePath = "C:/xampp/htdocs/database/AccessDB.accdb";
if (!file_exists($databasePath)) {
    die('指定されたデータベースファイルが存在しません。');
}
$dsn = "odbc:Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=$databasePath;";

try {
    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['token']) || $_POST['token'] !== $_SESSION['token']) {
            die("不正なリクエストです。");
        }

        $contactData = $_SESSION['contact'] ?? null;

        if (!$contactData) {
            die("セッションの有効期限が切れています。最初から入力してください。");
        }

        // 文字化け対策でUTF-8からShift-JIS変換にしました(何個かある)
        $customerId = $contactData['customer_id'];
        $email = $contactData['email'];
        $category = mb_convert_encoding($contactData['category'], "SJIS", "UTF-8");  // SJISに変換
        $message = mb_convert_encoding($contactData['message'], "SJIS", "UTF-8");    // SJISに変換
        $currentDateTime = date("Y-m-d H:i:s");

        // `contact` テーブルにデータを保存
        $sql = "INSERT INTO contact (customer_id, email, category, message, created_at)
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $customerId,
            $email,
            $category,
            $message,
            $currentDateTime
        ]);

        // 入力データをセッションに保存
        $_SESSION['contact'] = [
            'customer_id' => $_POST['member_id'],
            'email' => $_POST['email'],
            'category' => $_POST['category'],
            'message' => $_POST['message'],
        ];

        // セッションからデータを取得
        $contactData = $_SESSION['contact'] ?? [
            'customer_id' => '',
            'email' => '',
            'category' => '',
            'message' => '',
        ];

        // セッションのクリア
        unset($_SESSION['contact'], $_SESSION['token']);

        // 完了画面へリダイレクト
        header('Location: ../HEW_complete/contact_complete.php');
        exit;
    }

} catch (PDOException $e) {
    die("データベースエラー: " . $e->getMessage());
}

// セッションからデータを取得
$contactData = $_SESSION['contact'] ?? [
    'customer_id' => '',
    'email' => '',
    'category' => '',
    'message' => '',
];

// CSRFトークンを再生成
$_SESSION['token'] = bin2hex(random_bytes(32));
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>お問い合わせ確認 | げみねっと</title>
    <link rel="stylesheet" href="../../assets/scripts/reset.css"/>
    <link rel="stylesheet" href="contact_input.css">
    <script src="contact_confirmation.js" defer></script>
</head>

<body>
<?php include("../../assets/HEW_menu/menu.php"); ?>
    <div class="central-wrapper">
        <div id="confirmation-page">
            <h1>お問い合わせ確認ページ</h1>
            <p><strong>会員ID:</strong> <?php echo htmlspecialchars($contactData['customer_id']); ?></p>
            <p><strong>メールアドレス:</strong> <?php echo htmlspecialchars($contactData['email']); ?></p>
            <p><strong>お問い合わせカテゴリ:</strong> <?php echo htmlspecialchars($contactData['category']); ?></p>
            <p><strong>お問い合わせ本文:</strong><br><?php echo nl2br(htmlspecialchars($contactData['message'])); ?></p>

            <div class="button-container">
                <!-- 戻るボタン-->
                <form method="POST" action="contact_input.php">
                    <button type="submit">戻る</button>
                </form>

                <!-- 送信ボタン -->
                <form method="POST" action="contact_complete.php">
                    <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
                    <button type="submit" name="submit">送信</button>
                </form>
            </div>
        </div>
    </div>
</body>

<?php include("../../assets/HEW_footer/footer.php"); ?>

</html>
