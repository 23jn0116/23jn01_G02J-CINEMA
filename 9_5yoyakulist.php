<?php
require_once './helpers/MovieDAO.php';
require_once './helpers/KaiinDAO.php';
require_once './helpers/yoyakulistDAO.php';
require_once './helpers/SeatDAO.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
    // セッションから会員情報、映画情報、スケジュール情報を取得
    $kaiin = $_SESSION['kaiin'];
    $kno = $kaiin->kno;
}

$yoyaku_id = $_POST['yoyakuid'] ?? null;


$yoyakulistDAO = new yoyakulistDAO();
$movieDAO = new movieDAO();
$seatDAO = new SeatDAO();

// 予約情報を取得
$yoyakulist = $yoyakulistDAO->get_yoyakulist($yoyaku_id);


// 予約情報が空であればエラーページにリダイレクト
if (empty($yoyakulist)) {
    header("Location: 9_5Erroryoyaku.php");
    exit;
}

// $yoyakulist は配列なので、最初の要素を取得
$yoyakulist = $yoyakulist[0];  // 配列の最初の要素にアクセス

// 予約情報の取得
$mcode = $yoyakulist->mcode;  // オブジェクトとしてアクセス

// mcode が取得できているか確認
if ($mcode === null) {
    die("mcodeが正しく取得できませんでした。");
}

// 映画情報を取得
$movie = $movieDAO->get_mcode_movie($mcode);  // 映画情報を取得

// 予約日時、座席情報の取得
$playdate = $yoyakulist->playdate;  
$time = $yoyakulist->time;  

$seat_id = $yoyakulist->seat;

// 上映場所の取得
$areaname = $yoyakulist->areaname;

// チケット情報がある場合は、適切に取得
$ticket = isset($yoyakulist->ticket_type) ? $yoyakulist->ticket_type : '情報がありません';  

// 映画名と会員名の安全な取得
$movieName = isset($movie->mname) ? $movie->mname : '情報がありません';
$kaiinName = isset($kaiin->kananame) ? $kaiin->kananame : '情報がありません';

// 任意のカスタムメッセージを作成
$customMessage = "<----予約詳細---->\n 映画名：" . htmlspecialchars($movieName) . "\n会員名：" . htmlspecialchars($kaiinName) . "\n上映日：" . htmlspecialchars($playdate) ."\n上映時間：" . htmlspecialchars($time) ."\n席番号：" . htmlspecialchars($seat_id) ."\nです。";

// QRコードに埋め込む情報として現在のURLにカスタムメッセージを追加
$reservationInfo = $customMessage;

// GoQR.me APIのURLにデータをエンコードして渡す
$qrApiUrl = 'https://api.qrserver.com/v1/create-qr-code/?data=' . urlencode($reservationInfo) . '&size=300x300';
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>予約完了</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./css/yoyakuEnd.css"> <!-- 外部CSSをリンク -->
</head>
<body>
<?php include "header2.php"; ?>

<div class="container">
    <h1>ご予約いただきありがとうございます。</h1>
    
    <div class="reservation-details">
        <!-- 映画名と会員名の表示 -->
        <div class="info">映画名: <?php echo htmlspecialchars($movieName); ?></div>
        <div class="info">会員名: <?php echo htmlspecialchars($kaiinName); ?></div>

        <!-- 上映日を表示 -->
        <div class="info">
            <?php if ($playdate): ?>
                <div class="info">上映日: <?php echo htmlspecialchars($playdate); ?></div>
            <?php else: ?>
                <div class="info">上映日情報がありません</div>
            <?php endif; ?>
        </div>
        
        <strong>上映時間:</strong> 
        <?php 
            echo htmlspecialchars($time);
        ?>
        <br>

        <!-- 座席情報 -->
        <?php if (isset($seat_id)): ?> 
            <strong>席番号:</strong> <?= htmlspecialchars($seat_id) ?><br>
        <?php else: ?>
            <div>座席情報がありません。</div>
        <?php endif; ?>
    </div>

    <p><strong>場所:</strong><?= htmlspecialchars($areaname)?></p>

    <!-- QRコード表示 -->
    <div class="qr-code">
        <p><strong>QRコード:</strong></p>
        <img src="<?php echo $qrApiUrl; ?>" alt="QR Code" class="img-fluid">
    </div>

    <!-- 注意事項 -->
    <div class="note">
        <p><strong>注意事項</strong></p>
        <ul>
            <li>あらかじめQRコードのスクリーンショットをお願いいたします。</li>
            <li>必ずQRコードをお持ちになって劇場でお越しください。</li>
            <li>キャンセルはマイページよりお願いいたします。</li>
            <li>期日を過ぎますとチケットが無効になりますのでご注意ください。</li>
        </ul>
    </div>
    <button class="home-button" onclick="location.href='1_1index2.php'">ホームへ戻る</button>
    <button class="home-button" onclick="location.href='9_5yoyaku.php'">戻る</button>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
