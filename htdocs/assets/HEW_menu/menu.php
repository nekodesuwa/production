<?php
// セッションが開始されていない場合のみ開始
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ユーザーIDの取得（未ログインならnull）
$userId = $_SESSION['user_id'] ?? null;

$databasePath = "C:/xampp/htdocs/database/AccessDB.accdb";
if (!file_exists($databasePath)) {
    die('指定されたデータベースファイルが存在しません。');
}

// ODBC DSN 設定
$dsn = "odbc:Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=$databasePath;";

try {
    $pdo = new PDO($dsn, "", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ユーザー情報の取得
    if ($userId !== null) {
        $sql = "SELECT username AS nickname, points, icon_path FROM user_account WHERE user_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ユーザーが見つからない場合
    if (empty($user)) {
        $user = [
            'nickname' => 'ゲスト',
            'points' => 0,
            'icon_path' => '/assets/img/user/default.png'
        ];
    } else {
        // 文字コード変換
        $user['nickname'] = mb_convert_encoding($user['nickname'], "UTF-8", "SJIS");

        // 初期アイコンの適用
        if (empty($user['icon_path'])) {
            $user['icon_path'] = '/assets/img/user/default.png';
        } else {
            $user['icon_path'] = mb_convert_encoding($user['icon_path'], "UTF-8", "SJIS");
        }
    }

    // 検索クエリの処理
    $searchQuery = trim($_GET['search'] ?? '');
    if ($searchQuery === '') {
        $sql = "SELECT product_id, product_name, product_kana, product_roma, price_excluding_tax, image_url FROM product";
        $stmt = $pdo->query($sql);
    } else {
        $searchQuery = '%' . mb_convert_encoding($searchQuery, "SJIS", "UTF-8") . '%';
        $sql = "SELECT product_id, product_name, product_kana, product_roma, price_excluding_tax, image_url 
                FROM product
                WHERE product_name LIKE ? OR product_kana LIKE ? OR product_roma LIKE ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$searchQuery, $searchQuery, $searchQuery]);
    }

    // 商品データ取得
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($products as &$item) {
        $item['product_name'] = mb_convert_encoding($item['product_name'], "UTF-8", "SJIS");
        $item['product_kana'] = mb_convert_encoding($item['product_kana'], "UTF-8", "SJIS");
        $item['product_roma'] = mb_convert_encoding($item['product_roma'], "UTF-8", "SJIS");
    }
    unset($item);

} catch (PDOException $e) {
    die("データベースエラー: " . $e->getMessage());
}
?>


<link rel="stylesheet" href="../../assets/scripts/reset.css">
<link rel="stylesheet" href="../../assets/HEW_menu/menu.css">
<script src="../../assets/HEW_menu/menu.js" defer></script>

<div class="menu-container">
    <div class="menu-header">
        <div class="menu-logo">
            <a href="../../pages/HEW_index/index.php">
                <img src="../../assets/img/icon/logo.png" alt="サイトロゴ">
            </a>
        </div>
        <div class="menu-header-content">
            <form action="../../pages/HEW_products/products.php" method="GET" class="menu-search-box">
                <input type="text" name="search" placeholder="商品を検索..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                <button type="submit" class="menu-search"><img src="../../assets/img/icon/search.png" alt="検索"></button>
            </form>

            <div class="menu-login">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- ログイン時はアイコン画像を表示 -->
                    <a href="../../pages/HEW_mypage/mypage.php">
                    <img class="maru" src="<?php echo htmlspecialchars($user['icon_path']); ?>" alt="ユーザーアイコン" />
                        マイページ
                    </a>
                <?php else: ?>
                    <!-- 未ログイン時はログインボタンを表示 -->
                    <a href="../../pages/HEW_login/login.php">
                        <img src="../../assets/img/icon/login.png" alt="ログイン">ログイン
                    </a>
                <?php endif; ?>
            </div>

            <div class="menu-cart">
                <a href="../../pages/HEW_cart/cart.php">
                    <img src="../../assets/img/icon/cart.png" alt="カート">
                </a>
            </div>

            <div class="menu-hamburger">
                <div class="menu-hamburger-icon" id="menu-menuIcon">&#9776;</div>
            </div>
        </div>
    </div>

    <div id="menu-menu" class="menu-side-menu">
        <div class="menu-close-btn" id="menu-closeMenu">&times;</div>
        <ul>
            <li><a href="/pages/HEW_index/index.php">トップページ</a></li>
            <li><a href="/pages/HEW_mypage/mypage.php">マイページ</a></li>
            <li><a href="/pages/HEW_cart/cart.php">カート</a></li>
            <li><a href="/pages/HEW_order-history/order-history.php">注文履歴</a></li>
            <li><a href="/pages/HEW_entry/entry.php">ゲーム</a></li>
            <li><a href="/pages/HEW_record/record.php">戦績</a></li>
            <li><a href="/pages/HEW_Q&A/Q&A.php">Q&A</a></li>
            <li><a href="/pages/HEW_contact_precautions/contact_precautions.php">お問い合わせ</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="/pages/HEW_login/logout.php">ログアウト</a></li>
            <?php else: ?>
                <li><a href="/pages/HEW_login/login.php">ログイン</a></li>
            <?php endif; ?>
        </ul>
    </div>

    <div id="menu-overlay"></div>
</div>
