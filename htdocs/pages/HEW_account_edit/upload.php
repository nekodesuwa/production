<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // フォームデータの受け取り
    $userId = $_POST['user-id'] ?? '';
    $nickname = $_POST['nickname'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $optionalPhone = $_POST['optional-phone'] ?? '';
    $newsletter = isset($_POST['newsletter']) ? 1 : 0;

    // アイコンデータの処理
    $iconPath = '';
    if (!empty($_POST['icon-data'])) {
        $iconData = $_POST['icon-data'];

        list($type, $data) = explode(';', $iconData);
        list(, $data) = explode(',', $data);
        $imageData = base64_decode($data);

        $relativePath = '../../assets/img/user';
        $directory = __DIR__ . '/' . $relativePath;
        $iconPath = $relativePath . '/user' . $userId . '.png';

        if (!file_exists($directory)) {
            if (!mkdir($directory, 0777, true)) {
                die('ディレクトリ作成に失敗しました。');
            }
        }

        if (!file_put_contents($directory . '/user' . $userId . '.png', $imageData)) {
            die('画像の保存に失敗しました。');
        }
    }

    // データベース接続
    $databasePath = "C:/xampp/htdocs/database/AccessDB.accdb";
    if (!file_exists($databasePath)) {
        die('データベースファイルが存在しません。');
    }

    $dsn = "odbc:Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=$databasePath;";

    $username = "";
    $password = "";

    try {
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // 更新処理
        if ($iconPath !== '') {
            $sql = "UPDATE user_account SET nickname = ?, email = ?, phone = ?, optional_phone = ?, newsletter = ?, icon_path = ? WHERE user_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $nickname,
                $email,
                $phone,
                $optionalPhone,
                $newsletter,
                $iconPath,
                $userId,
            ]);
        } else {
            $sql = "UPDATE user_account SET nickname = ?, email = ?, phone = ?, optional_phone = ?, newsletter = ? WHERE user_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $nickname,
                $email,
                $phone,
                $optionalPhone,
                $newsletter,
                $userId,
            ]);
        }

        header('Location: ../HEW_complete/account_edit_complete.php');
        exit;
    } catch (PDOException $e) {
        die("データベースエラー: " . $e->getMessage());
    }
}
?>
