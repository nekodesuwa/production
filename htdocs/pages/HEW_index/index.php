<?php
$databasePath = "C:/xampp/htdocs/database/AccessDB.accdb";
if (!file_exists($databasePath)) {
    die('指定されたデータベースファイルが存在しません。');
}
$dsn = "odbc:Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=$databasePath;";

try {
    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // おすすめ商品の取得（最新4件）
    $sql = "SELECT TOP 4 product_id, product_name, price_excluding_tax, image_url 
            FROM product ORDER BY created_at DESC";
    $stmt = $pdo->query($sql);
    $recommendedProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 売れ筋商品の取得（売上情報の高い順に8件）
    $sql = "SELECT TOP 8 product_id, product_name, price_excluding_tax, image_url 
            FROM product ORDER BY sales_info DESC";
    $stmt = $pdo->query($sql);
    $bestSellingProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// お知らせの取得（最新5件）
$sql = "SELECT TOP 5 message, created_at FROM notifications ORDER BY created_at DESC";
$stmt = $pdo->query($sql);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 文字化け防止
    function convertEncodingArray(&$array, $fields) {
        foreach ($array as &$item) {
            foreach ($fields as $field) {
                if (isset($item[$field]) && !is_numeric($item[$field])) {
                    $item[$field] = mb_convert_encoding($item[$field], "UTF-8", "SJIS");
                }
            }
        }
    }

    convertEncodingArray($recommendedProducts, ['product_name']);
    convertEncodingArray($bestSellingProducts, ['product_name']);
    convertEncodingArray($notifications, ['message']);

} catch (PDOException $e) {
    die("データベースエラー: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TOP | げみねっと</title>
    <link rel="stylesheet" href="../../assets/scripts/reset.css"/>
    <link rel="stylesheet" href="index.css">
    <script src="index.js" defer></script>
</head>
<body>

<?php include("../../assets/HEW_menu/menu.php"); ?>

<main>
    <section id="event-banner">
        <div class="carousel">
            <div class="carousel-item active"><a href=""><img src="../../assets/img/banner/banner_1.png" alt="イベント1"></div></a>
            <div class="carousel-item"><a href="../HEW_introduction/introduction.php?product_id=47"><img src="../../assets/img/banner/banner_2.png" alt="イベント2"></div></a>
            <div class="carousel-item"><a href="../HEW_entry/entry.php"><img src="../../assets/img/banner/banner_3.png" alt="イベント3"></div></a>
        </div>
        <button class="carousel-control prev">❮</button>
        <button class="carousel-control next">❯</button>
    </section>

    <section id="recommended-products">
    <h2>おすすめ商品</h2>
    <div class="product-list">
    <?php if (!empty($recommendedProducts)): ?>
    <?php
    // 商品数を4つに制限
    $limitedProducts = array_slice($recommendedProducts, 0, 4);
    ?>
    <?php foreach ($limitedProducts as $product): ?>
        <div class="product-item">
            <a href="../HEW_introduction/introduction.php?product_id=<?php echo htmlspecialchars($product['product_id']); ?>">
                <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                <?php
                // 商品名が20文字を超える場合は「･･･」を表示
                $productName = mb_strimwidth($product['product_name'], 0, 20, "…", "UTF-8");
                ?>
                <p class="product-name"><?php echo htmlspecialchars($productName); ?></p>
                <p class="product-price" >¥<?php echo number_format($product['price_excluding_tax']); ?></p>
            </a>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p>おすすめ商品は現在ありません。</p>
<?php endif; ?>

    </div>
</section>


<section id="best-selling-products">
    <h2>売れ筋商品</h2>
    <div class="product-list">
        <?php if (!empty($bestSellingProducts)): ?>
            <?php
            // 商品数を8つに制限
            $limitedBestSellingProducts = array_slice($bestSellingProducts, 0, 8);
            ?>
            <?php foreach ($limitedBestSellingProducts as $product): ?>
                <div class="product-item">
                    <a href="../HEW_introduction/introduction.php?product_id=<?php echo htmlspecialchars($product['product_id']); ?>">
                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                        <?php
                        // 商品名が20文字を超える場合は「･･･」を表示
                        $productName = mb_strimwidth($product['product_name'], 0, 20, "…", "UTF-8");
                        ?>
                        <p class="title"><?php echo htmlspecialchars($productName); ?></p>
                        <p>¥<?php echo number_format($product['price_excluding_tax']); ?></p>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>売れ筋商品は現在ありません。</p>
        <?php endif; ?>
    </div>
</section>



<section id="notifications">
    <h2>お知らせ</h2>
    <div id="notification-list">
        <?php if (!empty($notifications)): ?>
            <?php foreach ($notifications as $note): ?>
                <p>
                    <strong><?php echo date("Y年m月d日 H:i", strtotime($note['created_at'])); ?></strong>
                    <br>
                    <?php echo htmlspecialchars($note['message']); ?>
                </p>
            <?php endforeach; ?>
        <?php else: ?>
            <p>現在お知らせはありません。</p>
        <?php endif; ?>
    </div>
</section>
</main>
<?php include("../../assets/HEW_footer/footer.php"); ?>
</body>
</html>
