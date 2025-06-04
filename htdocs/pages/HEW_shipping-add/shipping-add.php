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

    // フォームが送信された場合
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $deliveryName = mb_convert_encoding($_POST['delivery_name'] ?? '', "SJIS", "UTF-8");
        $phoneNumber = $_POST['phone_number'] ?? '';
        $postalCode = $_POST['postal_code'] ?? '';
        $prefectureId = $_POST['prefecture_id'] ?? '';
        $cityId = $_POST['city_id'] ?? '';
        $street = mb_convert_encoding($_POST['street'] ?? '', "SJIS", "UTF-8");
        $buildingName = mb_convert_encoding($_POST['building_name'] ?? '', "SJIS", "UTF-8");

        // バリデーション
        if (empty($deliveryName) || empty($phoneNumber) || empty($postalCode) || empty($prefectureId) || empty($cityId) || empty($street)) {
            die("エラー: 必須項目が未入力です。");
        }

        // `delivery_address` にデータを追加
        $sql = "INSERT INTO delivery_address (customer_id, prefecture_id, city_id, street, building_name, phone_number, delivery_name, postal_code, created_at, updated_at) 
                VALUES (:customer_id, :prefecture_id, :city_id, :street, :building_name, :phone_number, :delivery_name, :postal_code, NOW(), NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':customer_id' => $customerId,
            ':prefecture_id' => $prefectureId,
            ':city_id' => $cityId,
            ':street' => $street,
            ':building_name' => $buildingName,
            ':phone_number' => $phoneNumber,
            ':delivery_name' => $deliveryName,
            ':postal_code' => $postalCode
        ]);

        header('Location: ../HEW_shipping-list/shipping-list.php');
        exit;
    }

    // 都道府県リストを取得
    $stmt = $pdo->query("SELECT prefecture_id, prefecture_name FROM prefecture ORDER BY prefecture_id ASC");
    $prefectures = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 文字コード変換
    foreach ($prefectures as &$prefecture) {
        $prefecture['prefecture_name'] = mb_convert_encoding($prefecture['prefecture_name'], "UTF-8", "SJIS");
    }
    unset($prefecture);

} catch (PDOException $e) {
    die("データベースエラー: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>住所登録 | げみねっと</title>
    <link rel="stylesheet" href="../../assets/scripts/reset.css"/>
    <link rel="stylesheet" href="shipping-add.css">
</head>

<body>

<?php include("../../assets/HEW_menu/menu.php"); ?>

<div class="container">
    <div class="back-button-area">
        <a class="back-button-link" href="../HEW_shipping-list/shipping-list.php"><button class="back-button">＜ 戻る</button></a>
    </div>
    <div class="container-area">
        <h2>新しい住所を追加する</h2>
        <form method="POST" action="shipping-add.php">
            <div>
                <label for="delivery_name">受取人名</label>
                <input type="text" id="delivery_name" name="delivery_name" placeholder="例: 木村拓哉" required>
            </div>

            <div>
                <label for="phone_number">電話番号</label>
                <input type="text" id="phone_number" name="phone_number" placeholder="例: 080-1234-5678" required>
            </div>

            <div>
                <label for="postal_code">郵便番号</label>
                <input type="text" id="postal_code" name="postal_code" placeholder="111-1111" required>
                <button type="button" id="search_address">住所検索</button>
            </div>

            <div>
                <label for="prefecture_id">都道府県</label>
                <select id="prefecture_id" name="prefecture_id" required>
                    <option value="">選択してください</option>
                    <?php foreach ($prefectures as $prefecture): ?>
                        <option value="<?= htmlspecialchars($prefecture['prefecture_id']); ?>">
                            <?= htmlspecialchars($prefecture['prefecture_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="city_id">市区町村</label>
                <select id="city_id" name="city_id" required>
                    <option value="">市区町村を選択</option>
                </select>
            </div>

            <div>
                <label for="street">番地</label>
                <input type="text" id="street" name="street" placeholder="例: 1-1-1" required>
            </div>

            <div>
                <label for="building_name">建物名・部屋番号</label>
                <input type="text" id="building_name" name="building_name" placeholder="例: ○○マンション101号室">
            </div>

            <div class="add-button">
                <button type="submit">この住所を追加する</button>
            </div>
        </form>
    </div>
</div>

<script>
    let isComposing = false; // IME入力中フラグ

    function toHalfWidth(str) {
        return str.normalize("NFKC");
    }

    function addInputFilter(inputElement, filterFunction) {
        inputElement.addEventListener("compositionstart", () => {
            isComposing = true; // IME入力中
        });

        inputElement.addEventListener("compositionend", () => {
            isComposing = false; // IME入力完了
            applyFilter(inputElement, filterFunction);
        });

        inputElement.addEventListener("input", function () {
            if (!isComposing) { // IME変換中でなければ適用
                applyFilter(this, filterFunction);
            }
        });
    }

    // 全角文字を半角に変換
    function applyFilter(element, filterFunction) {
        let start = element.selectionStart; // カーソル位置を保存
        let end = element.selectionEnd;
        let newValue = filterFunction(element.value);

        if (element.value !== newValue) {
            setTimeout(() => {
                element.value = newValue;
                element.setSelectionRange(start, end); // カーソル位置を復元
            }, 0);
        }
    }

    // 各フィールドに適用
    addInputFilter(document.getElementById("delivery_name"), toHalfWidth);
    addInputFilter(document.getElementById("phone_number"), function (value) {
        return toHalfWidth(value).replace(/[^0-9-]/g, ""); // 数字とハイフンのみ許可
    });
    addInputFilter(document.getElementById("postal_code"), function (value) {
        return toHalfWidth(value).replace(/[^0-9]/g, ""); // 数字のみ許可
    });
    addInputFilter(document.getElementById("street"), toHalfWidth);
    addInputFilter(document.getElementById("building_name"), toHalfWidth);

    // **郵便番号検索ボタンの処理**
    document.getElementById("search_address").addEventListener("click", function () {
        const postalCode = document.getElementById("postal_code").value.replace("-", "").trim();
        if (postalCode.length !== 7) {
            alert("郵便番号は7桁で入力してください。");
            return;
        }

        fetch(`https://zipcloud.ibsnet.co.jp/api/search?zipcode=${postalCode}`)
            .then(response => response.json())
            .then(data => {
                if (data.results) {
                    const address = data.results[0];
                    document.getElementById("prefecture_id").value = address.prefcode;
                    loadCities(address.prefcode, address.city);
                } else {
                    alert("住所が見つかりませんでした。");
                }
            })
            .catch(error => console.error("エラー:", error));
    });

    // 都道府県選択時に市区町村リストを自動更新
    document.getElementById("prefecture_id").addEventListener("change", function () {
        const prefectureId = this.value;
        if (prefectureId) {
            loadCities(prefectureId, "");
        }
    });

    // 市区町村リストを取得する関数
    function loadCities(prefectureId, selectedCity) {
        fetch("get-cities.php?prefecture_id=" + prefectureId)
            .then(response => response.json())
            .then(data => {
                const citySelect = document.getElementById("city_id");
                citySelect.innerHTML = '<option value="">市区町村を選択</option>';

                data.forEach(city => {
                    const option = document.createElement("option");
                    option.value = city.city_id;
                    option.textContent = city.city_name;

                    if (city.city_name === selectedCity) {
                        option.selected = true;
                    }

                    citySelect.appendChild(option);
                });
            })
            .catch(error => console.error("エラー:", error));
    }
</script>
<?php include("../../assets/HEW_footer/footer.php"); ?>
</body>
</html>
