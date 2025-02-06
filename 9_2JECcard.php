<?php
// 必要なDAOクラスを読み込む
require_once '../helpers/KaiinDAO.php';  // 会員情報のDAO
require '../helpers/JECcardDAO.php';     // JECカード情報のDAO

// セッションが開始されていない場合、セッションを開始
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// セッションから会員情報を取得
$kaiin = !empty($_SESSION['kaiin']) ? $_SESSION['kaiin'] : null;

// 会員情報が取得できた場合、会員番号を取得
if ($kaiin && isset($kaiin->kno)) {
    $kno = $kaiin->kno; 
} else {
    // 会員情報が正しく取得できなかった場合のエラーメッセージ
    echo "<p>会員情報が正しく取得できませんでした。</p>";
    exit;
}

// JECカード情報の取得
$jeccardDAO = new JECcardDAO();
$jeccard = $jeccardDAO->getJECcard($kno);

// JECカード情報が存在する場合、残高を取得。存在しない場合は残高を0に設定
$zandaka = $jeccard ? $jeccard->zandaka : 0; 
// カード情報が見つからなかった場合のエラーメッセージ
$errorMessage = !$jeccard ? "カード情報が見つかりませんでした。" : ""; 

// POSTされたチャージ金額を取得。金額がない場合は0を設定
$selectedcharge = isset($_POST['selectedcharge']) ? intval(str_replace('￥', '', $_POST['selectedcharge'])) : 0; 

// 最大チャージ可能金額を計算 (500,000円 - 現在の残高)
$maxCharge = max(0, 500000 - $zandaka); 
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>J-CINEMA</title>
    <link href="../css/JECcard.css" rel="stylesheet">
    <script>
        // チャージ金額を設定するJavaScript関数
        function setCharge(amount) {
            document.getElementById('selectedcharge').value = "￥" + amount; // 表示用に金額をセット
            document.getElementById('selectedchargeInput').value = amount; // 実際のフォームに金額をセット
        }
    </script>
</head>
<body>

<!-- ヘッダーを読み込む -->
<?php include "header2.php"; ?>

<!-- 現在のJECカード残高を表示 -->
<p>日電カード残額</p>
<p><input type="text" value="￥<?php echo htmlspecialchars($zandaka); ?>" readonly></p> 

<!-- エラーメッセージがある場合に表示 -->
<?php if (!empty($errorMessage)): ?>
    <p style="color: red;"><?php echo htmlspecialchars($errorMessage); ?></p>
<?php endif; ?>

<!-- チャージ金額選択ボタンのセクション -->
<div class="button-container">
    <table>
        <!-- 複数の金額ボタンを表示 -->
        <tr>
            <td><button type="button" onclick="setCharge(1000)">￥1,000</button></td>
            <td><button type="button" onclick="setCharge(2000)">￥2,000</button></td>
            <td><button type="button" onclick="setCharge(3000)">￥3,000</button></td>
        </tr>
        <tr>
            <td><button type="button" onclick="setCharge(4000)">￥4,000</button></td>
            <td><button type="button" onclick="setCharge(5000)">￥5,000</button></td>
            <td><button type="button" onclick="setCharge(10000)">￥10,000</button></td>
        </tr>
    </table>
</div>

<!-- チャージ金額表示 -->
<p>チャージ金額</p>
<p><input type="text" id="selectedcharge" name="charge" value="￥<?php echo htmlspecialchars($selectedcharge); ?>" readonly></p>

<!-- チャージ可能金額表示 -->
<p>チャージ可能金額</p>
<p><input type="text" value="￥<?php echo htmlspecialchars($maxCharge); ?>" readonly></p> 

<!-- チャージ開始ボタンと戻るボタン -->
<div class="button-container">
    <table>
        <tr>
            <td>
                <!-- チャージ開始フォーム -->
                <form action="9_2JECcardcheck.php" method="POST">
                    <input type="hidden" id="selectedchargeInput" name="selectedcharge" value="">
                    <button type="submit" class="reserve-button">チャージ開始</button>
                </form>
            </td>
        </tr>
        <tr>
            <td>
                <!-- マイページに戻るボタン -->
                <button class="signup-button" onclick="location.href='9_1mypage.php'">戻る</button>
            </td>
        </tr>
    </table>
</div>

</body>
</html>
