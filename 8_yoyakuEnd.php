<?php
    // 必要なファイルをインクルード
    require '../helpers/MovieDAO.php';
    require_once '../helpers/KaiinDAO.php';
    require_once '../helpers/yoyakulistDAO.php';
    require_once '../helpers/SeatDAO.php';

    // セッションが開始されていない場合、開始
    if (session_status() === PHP_SESSION_NONE) {
        session_start();

        // セッションから会員情報、映画情報、スケジュール情報を取得
        $kaiin = $_SESSION['kaiin'];
        $age = $_SESSION['age'];
        $movie = $_SESSION['movie'];
        $playdate = $_SESSION['playdate'];
        $seat = $_SESSION['seat'];
        $sno = $_SESSION['selected_sno'];
        $time = $_SESSION['selected_time'];
        $areaname = $_SESSION['areaname'];
    }

    // 予約が完了していない場合のみ処理
    if ($_SESSION['purchase_complete'] !== true){ 

        // 安全にデータを取得（エラー防止）
        $movieName = $movie->mname ?? '情報がありません';
        $kaiinName = $kaiin->kananame ?? '情報がありません';

        // 任意のカスタムメッセージを作成
        $customMessage = "<----予約詳細---->\n 映画名：" . htmlspecialchars($movieName) . "\n会員名：" . htmlspecialchars($kaiinName) . "\n上映日：" . htmlspecialchars($playdate) ."\n上映時間：" . htmlspecialchars($time) . "\n席番号：" . htmlspecialchars($seat) . "\n場所：" . htmlspecialchars($areaname) . "\nです。";

        // QRコードに埋め込む情報として現在のURLにカスタムメッセージを追加
        $reservationInfo = $customMessage;

        // GoQR.me APIのURLにデータをエンコードして渡す
        $qrApiUrl = 'https://api.qrserver.com/v1/create-qr-code/?data=' . urlencode($reservationInfo) . '&size=300x300';

        // QRコードを画像ファイルとして保存
        $imageContent = file_get_contents($qrApiUrl);
        $directoryPath = '\\\\10.32.97.1\\Web\\SOTSU\\2024\\23JN01\\G02\\卒業制作\\images\\QRcode\\';
        $fileName = uniqid() . '.png'; // ユニークなファイル名を生成
        $fullPath = $directoryPath . $fileName; // フルパスを作成
        file_put_contents($fullPath, $imageContent); // 画像ファイルとして保存

        // QRコードのファイル名をデータベースに保存
        $yoyakuDAO = new yoyakulistDAO();
        $kno = $kaiin->kno; // 会員番号を取得

        // 予約情報をyoyakulistDAOに挿入
        $yoyakuDAO->insert_yoyaku($kno, $movie->mcode, $playdate, $seat, $time, $fileName, $areaname);

        // セッションをリセットし、再度開始
        session_unset(); // セッションの変数を全て解除
        session_destroy(); // セッションを破棄
        session_start(); // 新しいセッションを開始
        $_SESSION['kaiin']  = $kaiin;
        $_SESSION['age']    = $age;
        $_SESSION['id']     = $kaiin->kno;
        $_SESSION['kessai'] = 0;
        $_SESSION['purchase_complete'] = true; // 購入済みフラグを立てる
    } else {
        // すでに購入が完了している場合、エラーページにリダイレクト
        $_SESSION['purchase_complete'] = false; // 購入済みフラグを立てない
        header("Location: 7_ErrorKessai.php");
        exit;
    }
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>予約完了</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/yoyakuEnd.css"> <!-- 外部CSSをリンク -->
</head>
<body>
    <!-- ヘッダーをインクルード -->
    <?php include "header2.php"; ?>

    <div class="container">
        <h1>予約が完了しました！</h1>
        <p class="text-center">ご予約いただきありがとうございます。</p>
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
            
            <strong>上映時間:<?php echo htmlspecialchars($time); ?></strong> 
            <br>
            <!-- 座席情報 -->
            <?php if (isset($seat)): ?> 
                <strong>席番号:</strong> <?= htmlspecialchars($seat) ?><br>
            <?php else: ?>
                <div>座席情報がありません。</div>
            <?php endif; ?>
        </div>

        <p><strong>場所:</strong> <?= $areaname?></p>

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

        <!-- ホームへ戻るボタン -->
        <button class="home-button" onclick="location.href='1_1index2.php'">ホームへ戻る</button>
    </div>

    <!-- BootstrapのJavaScriptを読み込む -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
