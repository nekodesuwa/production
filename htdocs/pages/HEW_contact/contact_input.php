<?php
session_start();

// ログインしている場合、会員IDとメールアドレスを自動設定
$customerId = $_SESSION['user_id'] ?? '';
$email = $_SESSION['email'] ?? ''; // ログイン時のメールアドレスを取得する場合
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>お問い合わせ入力 | げみねっと</title>
    <link rel="stylesheet" href="../../assets/scripts/reset.css"/>
    <link rel="stylesheet" href="contact_input.css">
    <script src="contact_input.js" defer></script>
</head>
<body>
    <?php include("../../assets/HEW_menu/menu.php"); ?>
    <div id="contact-form">
        <h1>お問い合わせ入力ページ</h1>
        <form id="inquiry-form" method="POST" action="contact_confirmation.php">
            <!-- CSRF対策 -->
            <?php $_SESSION['token'] = bin2hex(random_bytes(32)); ?>
            <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">

            <label for="member-id">会員ID入力:</label>
            <input type="text" id="member-id" name="member_id" value="<?php echo htmlspecialchars($customerId); ?>" <?php echo $customerId ? 'readonly' : ''; ?> required><br>

            <label for="email">メールアドレス入力:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required><br>

            <label for="category">お問い合わせカテゴリの選択:</label>
            <select id="category" name="category" required>
                <option value="">選択してください</option>
                <option value="商品">商品について</option>
                <option value="配送">配送について</option>
                <option value="その他">その他</option>
            </select><br>

            <label for="message">お問い合わせ本文:</label>
            <textarea id="message" name="message" rows="5" required></textarea><br>

            <div class="button-container">
                <button type="submit" class="blue-button">次へ</button>
            </div>
        </form>
    </div>
</body>
<?php include("../../assets/HEW_footer/footer.php"); ?>
</html>
