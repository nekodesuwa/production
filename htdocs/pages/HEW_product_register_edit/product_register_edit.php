<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['product_title'] ?? '';
    $kana = $_POST['product_kana'] ?? '';
    $genreId = $_POST['product_genre'] ?? '';
    $price = $_POST['product_price'] ?? 0;
    $description = $_POST['product_description'] ?? '';
    $detailedDescription = $_POST['product_detailed_description'] ?? '';

    // 税抜価格を計算
    $priceExcludingTax = round($price / 1.1);

    // 画像アップロード処理
    $uploadDir = '../../uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true); // ディレクトリがない場合は作成
    }

    $imageUrls = [];
    for ($i = 1; $i <= 3; $i++) {
        if (isset($_FILES["product_image_$i"]) && $_FILES["product_image_$i"]['error'] === UPLOAD_ERR_OK) {
            $fileName = basename($_FILES["product_image_$i"]['name']);
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES["product_image_$i"]['tmp_name'], $targetPath)) {
                $imageUrls[] = $targetPath;
            } else {
                die("画像 $i のアップロードに失敗しました。");
            }
        } else {
            $imageUrls[] = ''; // 画像が未アップロードの場合は空白
        }
    }

    // データベース接続
    $databasePath = "C:/xampp/htdocs/database/AccessDB.accdb";
    if (!file_exists($databasePath)) {
        die('指定されたデータベースファイルが存在しません。');
    }
    $dsn = "odbc:Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=$databasePath;AutoTranslate=No;";

    try {
        $pdo = new PDO($dsn);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // SQLインジェクション対策 & Shift-JISへ変換
        $sql = "INSERT INTO product (
                    product_name,
                    product_kana,
                    genre_id,
                    price,
                    price_excluding_tax,
                    description,
                    detailed_description,
                    image_url,
                    image_url_2,
                    image_url_3,
                    created_at,
                    updated_at
                ) VALUES (
                    :title,
                    :kana,
                    :genre_id,
                    :price,
                    :price_excluding_tax,
                    :description,
                    :detailed_description,
                    :image_url,
                    :image_url_2,
                    :image_url_3,
                    NOW(),
                    NOW()
                )";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':title' => mb_convert_encoding($title, "SJIS", "UTF-8"),
            ':kana' => mb_convert_encoding($kana, "SJIS", "UTF-8"),
            ':genre_id' => $genreId,
            ':price' => $price,
            ':price_excluding_tax' => $priceExcludingTax,
            ':description' => mb_convert_encoding($description, "SJIS", "UTF-8"),
            ':detailed_description' => mb_convert_encoding($detailedDescription, "SJIS", "UTF-8"),
            ':image_url' => mb_convert_encoding($imageUrls[0], "SJIS", "UTF-8"),
            ':image_url_2' => mb_convert_encoding($imageUrls[1], "SJIS", "UTF-8"),
            ':image_url_3' => mb_convert_encoding($imageUrls[2], "SJIS", "UTF-8"),
        ]);

        // 正常終了後、完了画面にリダイレクト
        header('Location: product_upload_complete.php');
        exit;
    } catch (PDOException $e) {
        die("データベースエラー: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>商品登録 | げみねっと</title>
    <link rel="stylesheet" href="../../assets/scripts/reset.css"/>
    <link rel="stylesheet" href="product_register_edit.css">
    <script src="product_register_edit.js" defer></script>
</head>
<body>

<?php include("../../assets/HEW_menu/menu.php"); ?>

<div class="container">
    <form action="product_register_edit.php" method="POST" enctype="multipart/form-data">
        <div class="input-section">
            <label for="image-upload-1">メイン画像をアップロード</label>
            <input type="file" id="image-upload-1" name="product_image_1" accept="image/*" required>
        </div>

        <div class="input-section">
            <label for="image-upload-2">詳細画像 1 をアップロード</label>
            <input type="file" id="image-upload-2" name="product_image_2" accept="image/*">
        </div>

        <div class="input-section">
            <label for="image-upload-3">詳細画像 2 をアップロード</label>
            <input type="file" id="image-upload-3" name="product_image_3" accept="image/*">
        </div>

        <div class="input-section">
            <label for="title-input">タイトルを入力</label>
            <input type="text" id="title-input" name="product_title" required>
        </div>

        <div class="input-section">
            <label for="kana-input">ふりがなを入力</label>
            <input type="text" id="kana-input" name="product_kana">
        </div>

        <div class="input-section">
            <label for="genre-select">ジャンルを選択</label>
            <select id="genre-select" name="product_genre">
                <option value="1">家電</option>
                <option value="2">衣類</option>
                <option value="3">家具</option>
                <option value="4">本</option>
                <option value="5">食品</option>
                <option value="6">その他</option>
            </select>
        </div>

        <div class="input-section">
            <label for="price-input">値段を入力</label>
            <input type="number" id="price-input" name="product_price" min="0" required>
        </div>

        <div class="input-section">
            <label for="description-input">商品の説明</label>
            <textarea id="description-input" name="product_description"></textarea>
        </div>

        <div class="input-section">
            <label for="detailed-description-input">商品の詳細説明</label>
            <textarea id="detailed-description-input" name="product_detailed_description"></textarea>
        </div>

        <button type="submit" id="next-button">登録</button>
    </form>
</div>

<?php include("../../assets/HEW_footer/footer.php"); ?>

</body>
</html>
