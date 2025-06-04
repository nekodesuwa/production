<?php
// データベース接続
$databasePath = "C:/xampp/htdocs/database/AccessDB.accdb";
if (!file_exists($databasePath)) {
    die('指定されたデータベースファイルが存在しません。');
}
$dsn = "odbc:Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=$databasePath;";
$username = "";
$password = "";

$precautions = [];

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sql = "SELECT question, answer FROM faq WHERE category = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(1, '注意事項', PDO::PARAM_STR);
    $stmt->execute();

    $precautions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("データベースエラー: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>お問い合わせ注意事項 | げみねっと</title>
    <link rel="stylesheet" href="../../assets/scripts/reset.css"/>
    <link rel="stylesheet" href="contact_precautions.css">
    <script src="contact_precautions.js" defer></script>
</head>
<body>
<?php include("../../assets/HEW_menu/menu.php"); ?>
    <main class="precautions-container">
        <h1>お問い合わせ注意事項</h1>

        <section class="qa-section">
            <h2>Q&Aページはご覧なられましたか?</h2>
            <p>お問い合わせの前に、よくある質問（Q&A）ページを一度ご確認いただくと、早く解決できるかもしれません。</p>
            <button class="qa-btn" onclick="location.href='/pages/HEW_Q&A/Q&A.php'">Q&Aページへ移動</button>
        </section>

        <h2>注意事項</h2>
        <section class="precautions-list">
        <div class="scrollable-content">
        <ul>
            <li>
                <strong>回答までの時間について</strong><br>
                お問い合わせの内容によっては、回答まで数日お時間をいただく場合がございます。
            </li>
            <li>
                <strong>営業時間外の対応について</strong><br>
                土日祝日および年末年始はお問い合わせへの対応を行っておりません。翌営業日以降に対応させていただきます。
            </li>
            <li>
                <strong>個人情報の取り扱い</strong><br>
                お問い合わせ時にご提供いただいた個人情報は、お問い合わせへの対応および関連するご連絡にのみ使用いたします。
            </li>
            <li>
                <strong>フォーム記入の注意</strong><br>
                正確な情報をご記入いただけない場合、回答が遅れる、または回答できない場合があります。
            </li>
            <li>
                <strong>対応可能な内容について</strong><br>
                製品やサービスに関するお問い合わせのみ受け付けております。営業目的のご連絡はご遠慮ください。
            </li>
            <li>
                <strong>重複送信のご遠慮</strong><br>
                同一内容のお問い合わせを複数回送信されると、対応が遅れる場合があります。ご了承ください。
            </li>
            <li>
                <strong>特定の質問に関する制限</strong><br>
                サービスの運営方法や技術的な内部情報に関するお問い合わせにはお答えできません。
            </li>
            <li>
                <strong>第三者情報の記載禁止</strong><br>
                お問い合わせ内容に第三者の個人情報や秘密情報を記載しないでください。
            </li>
            <li>
                <strong>返信メールが届かない場合</strong><br>
                迷惑メールフォルダをご確認のうえ、再度ご連絡ください。受信設定で「@example.com」を許可してください。
            </li>
            <li>
                <strong>お問い合わせ内容の保存</strong><br>
                お問い合わせ内容は、対応履歴として一定期間保存させていただく場合があります。
            </li>
        </ul>
    </div>
    </section>
    <button id="contact-btn" class="contact-btn" onclick="location.href='../HEW_contact/contact_input.php'">お問い合わせページへ移動</button>
    </main>
<?php include("../../assets/HEW_footer/footer.php"); ?>
</body>
</html>
