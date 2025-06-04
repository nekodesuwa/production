<?php
session_start();

// ログインチェック
if (!isset($_SESSION['user_id'])) {
    header('Location: ../HEW_login/login.php');
    exit;
}

$customerId = $_SESSION['user_id'];

// データベース接続
$databasePath = "C:/xampp/htdocs/database/AccessDB.accdb";
if (!file_exists($databasePath)) {
    die('指定されたデータベースファイルが存在しません。');
}
$dsn = "odbc:Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=$databasePath;";

try {
    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    //  プレイヤーのステ取得
    $sql = "SELECT attack, health, speed, battle_available, match_count, win_count FROM game WHERE customer_id = :customer_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':customer_id' => $customerId]);
    $player = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$player) {
        die("<p>エラー: プレイヤーデータが見つかりません。</p>");
    }

    //  戦闘可能回数チェック  0ならエラー
    if ($player['battle_available'] <= 0) {
        die("<p>エラー: 戦闘可能回数がありません。購入により回数を追加してください。</p>");
    }

    //  戦闘開始時にmatch_count + 1
    $sql = "UPDATE game SET match_count = match_count + 1 WHERE customer_id = :customer_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':customer_id' => $customerId]);

    //  戦闘可能回数を1減らす
    $sql = "UPDATE game SET battle_available = battle_available - 1 WHERE customer_id = :customer_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':customer_id' => $customerId]);

    //  ランダムな敵の取得(β版ゆえ)
    $sql = "SELECT TOP 1 enemy_id, enemy_name, attack, health, speed
            FROM enemy ORDER BY RND(-Timer()*enemy_id)";
    $stmt = $pdo->query($sql);
    $enemy = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$enemy) {
        die("<p>エラー: 敵データが見つかりません。</p>");
    }

    foreach ($enemy as $key => $value) {
        if (!is_numeric($value)) {
            $enemy[$key] = mb_convert_encoding($value, "UTF-8", "SJIS");
        }
    }

    //  バトルログの登録
    $opponentId = $enemy['enemy_id'];
    $result = "未決定";
    $totalDamageDealt = 0;
    $totalDamageReceived = 0;
    $duration = 0;

    $sql = "INSERT INTO battle_log (customer_id, opponent_id, result, battle_date, damage_dealt, damage_received, duration, created_at, updated_at) 
            VALUES (:customer_id, :opponent_id, :result, NOW(), :damage_dealt, :damage_received, :duration, NOW(), NOW())";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':customer_id' => $customerId,
        ':opponent_id' => $opponentId,
        ':result' => $result,
        ':damage_dealt' => $totalDamageDealt,
        ':damage_received' => $totalDamageReceived,
        ':duration' => $duration,
    ]);

} catch (PDOException $e) {
    die("データベースエラー: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>対戦ページ | げみねっと</title>
    <link rel="stylesheet" href="game.css">
    <link rel="stylesheet" href="../../assets/scripts/reset.css"/>
    <script>
// プレイヤーと敵のステータスを外部から受け取る
let playerStats = {
    hp: <?php echo $player['health']; ?>,
    maxHp: <?php echo $player['health']; ?>,
    attack: <?php echo $player['attack']; ?>,
    speed: <?php echo $player['speed']; ?>
};

// ランダムな倍率を計算する関数
function getRandomMultiplier(min, max) {
    return Math.random() * (max - min) + min;
}

// 敵のステ計算
let enemyStats = {
    hp: Math.round(playerStats.hp * getRandomMultiplier(0.8, 1.1)),
    maxHp: 0,
    attack: Math.round(playerStats.attack * getRandomMultiplier(0.8, 1.1)),
    speed: Math.round(playerStats.speed * getRandomMultiplier(0, 2))
};

// let enemyStats = {
//     hp: <?php echo $enemy['health']; ?>,
//     maxHp: <?php echo $enemy['health']; ?>,
//     attack: <?php echo $enemy['attack']; ?>,
//     speed: <?php echo $enemy['speed']; ?>
// };

// maxHpをhpと同じに設定
enemyStats.maxHp = enemyStats.hp;

// フラグ諸々
let playerBuff = false; // プレイヤーのカウンター
let playerMeditateBuff = false; // プレイヤーの瞑想
let enemyBuff = false; // 敵のカウンター
let enemyMeditateBuff = false; // 敵の瞑想
let isGameOver = false; // ゲームオーバー

// ステータスを設定用の関数
function setPlayerStats(hp, maxHp, attack, speed) {
    playerStats.hp = hp;
    playerStats.maxHp = maxHp;
    playerStats.attack = attack;
    playerStats.speed = speed;
}

function setEnemyStats(hp, maxHp, attack, speed) {
    enemyStats.hp = hp;
    enemyStats.maxHp = maxHp;
    enemyStats.attack = attack;
    enemyStats.speed = speed;
}

// HPを減少させる関数
async function animateHPBar(target, fromHP, toHP, duration) {
    const steps = 30;
    const stepDuration = duration / steps;
    const hpChangePerStep = (fromHP - toHP) / steps;

    const characterImage_shake = document.getElementById("player-image"); // プレイヤーのキャラ画像
    characterImage_shake.classList.add("shake");

    const characterImage = document.getElementById("player-image");
    const enemyImage = document.getElementById("enemy-image");

    if (playerMeditateBuff) {
        characterImage.classList.add("glow");
    }
    if (enemyMeditateBuff) {
        enemyImage.classList.add("glow");
    }

    for (let i = 0; i < steps; i++) {
        fromHP -= hpChangePerStep;

        // HPが0を下回らないように調整
        if (fromHP < 0) {
            fromHP = 0;
        }

        // HPバーの変更に合わせてHPの残数も更新
        if (target.id === "player-hp-bar") {
            const percentage = (fromHP / playerStats.maxHp) * 100; // プレイヤーのHPを割合に
            target.style.width = Math.floor(percentage) + "%"; // 幅を設定
            document.getElementById("player-hp-text").textContent = "HP: " + Math.floor(fromHP); // HPテキスト更新
        } else if (target.id === "enemy-hp-bar") {
            const percentage = (fromHP / enemyStats.maxHp) * 100; // 敵のHPを割合に
            target.style.width = Math.floor(percentage) + "%"; // 幅を設定
            document.getElementById("enemy-hp-text").textContent = "HP: " + Math.floor(fromHP); // HPテキスト更新
        }

        await waitFor(stepDuration);
    }

    // HPが0以下にならないように
    if (toHP < 0) {
        toHP = 0;
    }

    // 最終的なHPバーの変更
    if (target.id === "player-hp-bar") {
        const percentage = (toHP / playerStats.maxHp) * 100; // プレイヤーの最終HPを割合に
        target.style.width = Math.floor(percentage) + "%";
        document.getElementById("player-hp-text").textContent = "HP: " + Math.floor(toHP);
    } else if (target.id === "enemy-hp-bar") {
        const percentage = (toHP / enemyStats.maxHp) * 100; // 敵の最終HPを割合に
        target.style.width = Math.floor(percentage) + "%";
        document.getElementById("enemy-hp-text").textContent = "HP: " + Math.floor(toHP);
    }

    characterImage_shake.classList.remove("shake");

    if (!playerMeditateBuff) {
        characterImage.classList.remove("glow");
    }
    if (!enemyMeditateBuff) {
        enemyImage.classList.remove("glow");
    }
}



function updateHPBars() {
    document.getElementById("player-hp-text").textContent = "HP: " + Math.floor(playerStats.hp);
    document.getElementById("enemy-hp-text").textContent = "HP: " + Math.floor(enemyStats.hp);
}

function logAction(playerAction, enemyAction) {
    const logDiv = document.getElementById("log");
    logDiv.innerHTML = "プレイヤー: " + playerAction + "<br><?php echo $enemy['enemy_name']; ?>: " + enemyAction;
}

// 0.3秒CT
function waitFor(time) {
    return new Promise(resolve => setTimeout(resolve, time));
}

// ボタンの有効/無効を切り替える関数
function toggleButtons(enabled) {
    const buttons_1 = document.querySelectorAll(".attack-button");
    buttons_1.forEach(button => {
        button.disabled = !enabled;
    });
    const buttons_2 = document.querySelectorAll(".counter-button");
    buttons_2.forEach(button => {
        button.disabled = !enabled;
    });
    const buttons_3 = document.querySelectorAll(".meditate-button");
    buttons_3.forEach(button => {
        button.disabled = !enabled;
    });
}


// 敵の行動
async function decideEnemyAction() {
    let enemyChoice = Math.random();
    let enemyAction = "";

    if (enemyChoice < 0.33) {
        enemyBuff = true; // 敵がカウンターを選択
        enemyAction = "カウンターを準備した！";
    } else if (enemyChoice < 0.66) {
        let damage = Math.floor(Math.random() * 10) + enemyStats.attack;
        if (enemyMeditateBuff) {
            damage *= 1.5;
            enemyMeditateBuff = false;
        }
        const oldPlayerHP = playerStats.hp;
        playerStats.hp -= Math.floor(damage);
        playerStats.hp = Math.max(playerStats.hp, 0); // HPが0未満にならないように調整
        // 震えるアニメーションを追加
        const characterImage_shake = document.getElementById("player-image"); // プレイヤーのキャラ画像
        characterImage_shake.classList.add("shake");
        await animateHPBar(document.getElementById("player-hp-bar"), oldPlayerHP, playerStats.hp, 300);
        enemyAction = "攻撃して" + damage + "のダメージ！";
        // フラグをリセット
        enemyBuff = false;
    } else {
        let heal = Math.floor(enemyStats.maxHp / 10);
        const oldEnemyHP = enemyStats.hp;
        enemyStats.hp = Math.min(enemyStats.hp + heal, enemyStats.maxHp);
        enemyMeditateBuff = true;
        await animateHPBar(document.getElementById("enemy-hp-bar"), oldEnemyHP, enemyStats.hp, 300);
        enemyAction = "瞑想して" + heal + "HP回復！";
        // フラグをリセット
        enemyBuff = false;
    }

    return enemyAction; // 敵の行動を返す
}

// 敵が素早さが高い場合、開始直後に攻撃する関数
async function enemyFirstAttackIfFaster() {
    if (enemyStats.speed > playerStats.speed) {
        let damage = Math.floor(Math.random() * 10) + enemyStats.attack;
        const oldPlayerHP = playerStats.hp;
        playerStats.hp -= Math.floor(damage);
        playerStats.hp = Math.max(playerStats.hp, 0); // HPが0未満にならないように調整
        await animateHPBar(document.getElementById("player-hp-bar"), oldPlayerHP, playerStats.hp, 300);
        logAction("先制されて" + damage + "のダメージ！", "先制攻撃！");
    }
    else {
        logAction("どの行動がいいかな……", "戦闘開始！");
    }
}
// プレイヤーの攻撃
async function playerAttack() {
    updateTurnCount();
    if (isGameOver) return;
    toggleButtons(false);

    let damage = Math.floor(Math.random() * 10) + playerStats.attack;

    if (playerMeditateBuff) {
        damage *= 1.5;
        playerMeditateBuff = false;
    }

    let playerAction = "攻撃して" + damage + "のダメージ！";

    // 敵の行動を決定する
    let enemyAction = await decideEnemyAction();
    logAction(playerAction, enemyAction);
    await waitFor(300);

    // 震えるアニメーションを追加
    const enemyImage_shake = document.getElementById("enemy-image"); // 敵のキャラ画像
    enemyImage_shake.classList.add("shake");

    // 敵の行動に応じた処理
    if (enemyBuff) {
        // 敵にダメージを与え、プレイヤーにもダメージを与える
        const counterDamage = Math.floor(damage * 1.2); // ダメージを1.2倍にして計算
        const oldPlayerHP = playerStats.hp;
        playerStats.hp -= Math.floor(damage); // プレイヤーが受けるダメージ
        playerStats.hp = Math.max(playerStats.hp, 0);
        const oldEnemyHP = enemyStats.hp;
        enemyStats.hp -= counterDamage; // 敵に返すダメージ

        await animateHPBar(document.getElementById("enemy-hp-bar"), oldEnemyHP, enemyStats.hp, 300);
        await animateHPBar(document.getElementById("player-hp-bar"), oldPlayerHP, playerStats.hp, 300);
        logAction(playerAction, "カウンターで" + counterDamage + "のダメージをプレイヤーに与えた！");

        // カウンター後はフラグをリセット
        enemyBuff = false;
    } else {
        const oldEnemyHP = enemyStats.hp;
        enemyStats.hp -= Math.floor(damage);
        enemyStats.hp = Math.max(enemyStats.hp, 0);
        await animateHPBar(document.getElementById("enemy-hp-bar"), oldEnemyHP, enemyStats.hp, 300);
    }

    updateHPBars();
    toggleButtons(true);
    checkGameOver();
}

// プレイヤーのカウンター
async function playerCounter() {
    updateTurnCount();
    if (isGameOver) return;
    toggleButtons(false);

    // カウンター準備の設定
    let playerAction = "カウンターを準備した！";
    playerBuff = true;  // カウンター準備状態をセット
    logAction(playerAction, "");
    await waitFor(300);

    // 敵の行動を決定し、行動テキストを格納
    let enemyAction = await decideEnemyAction();

    // カウンターが発動するかどうかを確認
    if (playerBuff && enemyAction.includes("攻撃")) {
        const damageplus = 0
        // 敵の攻撃ダメージを計算
        if (enemyMeditateBuff) {
            damageplus = enemyStats.attack * 0.5;
        }
        const damageReceived = Math.floor(Math.random() * 10) + enemyStats.attack + damageplus;

        // プレイヤーのカウンターダメージを、受けたダメージに基づいて計算
        const counterDamage = Math.floor(damageReceived * 1.2);
        const oldEnemyHP = enemyStats.hp;
        enemyStats.hp -= counterDamage;
        enemyStats.hp = Math.max(enemyStats.hp, 0); // HPが0未満にならないように調整

        // HPバー更新
        await animateHPBar(document.getElementById("enemy-hp-bar"), oldEnemyHP, enemyStats.hp, 300);

        // カウンター成功メッセージ
        logAction(playerAction, "カウンター成功！" + counterDamage + "のダメージを<?php echo $enemy['enemy_name']; ?>に与えた！");

        // カウンターフラグをリセット
        playerBuff = false;
    } else if (enemyAction.includes("攻撃")) {
        // 敵が攻撃しているがカウンターが無効な場合の処理
        const damage = Math.floor(Math.random() * 10) + enemyStats.attack;
        const oldPlayerHP = playerStats.hp;
        playerStats.hp -= damage;
        playerStats.hp = Math.max(playerStats.hp, 0); // HPが0未満にならないように調整
        await animateHPBar(document.getElementById("player-hp-bar"), oldPlayerHP, playerStats.hp, 300);

        logAction(playerAction, enemyAction + " プレイヤーに" + damage + "のダメージ！");
    } else {
        // 敵が攻撃以外の行動を選択した場合の処理
        logAction(playerAction, enemyAction);
    }

    // HPバーやボタンを更新
    updateHPBars();
    toggleButtons(true);
    checkGameOver();
}


// プレイヤーの瞑想
async function playerMeditate() {
    updateTurnCount();
    if (isGameOver) return;
    toggleButtons(false);

    let heal = Math.floor(playerStats.maxHp / 10);
    const oldPlayerHP = playerStats.hp;
    playerStats.hp = Math.min(playerStats.hp + heal, playerStats.maxHp);
    let playerAction = "瞑想して" + heal + "HP回復！";

    playerMeditateBuff = true;

    // プレイヤーの行動をログに表示
    logAction(playerAction, "");  // 敵の行動が決まる前にプレイヤーの瞑想行動を表示
    await animateHPBar(document.getElementById("player-hp-bar"), oldPlayerHP, playerStats.hp, 300);
    await waitFor(300);  // 瞑想アニメーションを少し遅らせることで他のログと重ならないようにする

    // 敵の行動を決定
    let enemyAction = await decideEnemyAction();
    logAction(playerAction, enemyAction);  // 瞑想ログを残しつつ敵の行動を追加

    updateHPBars();
    toggleButtons(true);
    checkGameOver();
}


// ゲームオーバーの判定
function checkGameOver() {
    if (enemyStats.hp <= 0) {
        isGameOver = true;
        // ターン数をlocalStorageに保存
        localStorage.setItem("turnCount", turnCount);

        //  勝利時に win_count + 1 する PHP を呼び出し
        fetch('update_win_count.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `customer_id=${<?php echo $customerId; ?>}`
        });
        setTimeout(() => {
            window.location.href = `../HEW_result/victory.php?turnCount=${turnCount}`; // ターンカウントをURLに追加
        }, 1000); // 1秒後に遷移
        alert("ゲームクリア！ プレイヤーは勝利しました");
    } else if (playerStats.hp <= 0) {
        isGameOver = true;
        // ターン数をlocalStorageに保存
        localStorage.setItem("turnCount", turnCount);
        setTimeout(() => {
            window.location.href = `../HEW_result/defeat.php?turnCount=${turnCount}`; // ターンカウントをURLに追加
        }, 1000); // 1秒後に遷移
        alert("ゲームオーバー！ プレイヤーは敗北しました");
    }
}

// ゲーム開始時に敵の行動を決定
async function startGame() {
    toggleButtons(true);
    await enemyFirstAttackIfFaster(); // 敵が素早さで先制攻撃をするか確認
    updateHPBars(); // HPバーを初期状態に更新
}

// ページ読み込み時にゲーム開始
window.onload = function () {
    startGame();
};

let turnCount = 1;

// ターン数を更新する関数
function updateTurnCount() {
    turnCount++;
    document.getElementById("turn-number").textContent = `${turnCount}ターン`;
}


// チュートリアルのツールチップを制御するスクリプト
document.querySelector('.info-icon-container').addEventListener('mouseover', () => {
    const tooltip = document.querySelector('.tooltip');
    tooltip.style.display = 'block';
});

document.querySelector('.info-icon-container').addEventListener('mouseleave', () => {
    const tooltip = document.querySelector('.tooltip');
    tooltip.style.display = 'none';
});

    </script>

</head>
<body>

    <?php include("../../assets/HEW_menu/menu.php"); ?>

    <div class="info-icon-container">
    <div class="info-icon">ℹ️</div>
    <div class="tooltip">
        <p>～ゲームの遊び方～</p>
        <ul>
            <li>「攻撃」: 敵にダメージを与えます。</li>
            <li>「カウンター」: 敵から受けたダメージを、1.2倍にして反撃します。</li>
            <li>「瞑想」: HPを少し回復し、攻撃力を上昇させる。</li>
        </ul>
    </div>
</div>


    <div class="container">
    <div class="turn-counter">
        <p id="turn-number">1ターン</p>
    </div>
    <div class="status-container">
        <div class="player-status">
            <h2>プレイヤー</h2>
            <div class="health-bar">
                <div id="player-hp-bar"></div>
            </div>
            <p id="player-hp-text">HP: <?php echo 100 + $player['health']; ?></p>
            <img src="../../assets/img/game/player-image.png" alt="Player Character" class="character-image" id="player-image">
        </div>
        <div class="enemy-status">
            <h2><?php echo $enemy['enemy_name']; ?></h2>
            <div class="health-bar">
                <div id="enemy-hp-bar"></div>
            </div>
            <p id="enemy-hp-text">HP: <?php echo $enemy['health']; ?></p>
            <img src="../../assets/img/game/enemy-image_<?php echo rand(1, 15)?>.png" alt="" class="character-image" id="enemy-image">
        </div>
    </div>
    </div>


    <div class="commands">
        <button class="attack-button" onclick="playerAttack()">攻撃</button>
        <button class="counter-button" onclick="playerCounter()">カウンター</button>
        <button class="meditate-button" onclick="playerMeditate()">瞑想</button>
    </div>

    <div id="log"></div>
    <div id="overlay"></div>
</body>

<?php include("../../assets/HEW_footer/footer.php"); ?>
</html>
