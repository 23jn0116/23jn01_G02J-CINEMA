<?php
require '../helpers/JECcardDAO.php';
require_once '../helpers/AreaDAO.php';
require_once '../helpers/KaiinDAO.php';
require_once '../helpers/Seat_yoyakuDAO.php';
require_once '../helpers/SeatDAO.php';
require_once '../helpers/PaymentDAO.php'; // PaymentDAOをインクルード
require_once '../helpers/yoyakulistDAO.php';
require_once '../helpers/MovieDAO.php';

session_start(); // セッション開始

// セッションに価格を保存
if (isset($_POST['totalPrice']) && is_numeric($_POST['totalPrice'])) {
    $_SESSION['price'] = (int)$_POST['totalPrice']; // 価格をセッションに格納
}

// 購入完了していない場合に処理
if ($_SESSION['purchase_complete'] !== true) { 
    // セッションから価格やその他の情報を取得
    $price = isset($_SESSION['price']) ? $_SESSION['price'] : 0; // 価格
    $kaiin = !empty($_SESSION['kaiin']) ? $_SESSION['kaiin'] : null; // 会員情報
    $sno = isset($_SESSION['selected_sno']) ? $_SESSION['selected_sno'] : null; // スクリーン番号
    $playdate = $_SESSION['playdate']; // 上映日
    $seat = $_SESSION['seat']; // 座席
    $time = $_SESSION['selected_time']; // 時間
    $mcode = $_SESSION['movie']->mcode; // 映画コード

    // 会員情報が無ければログインページにリダイレクト
    if ($kaiin && isset($kaiin->kno)) {
        $kno = $kaiin->kno; // 会員番号
    } else {
        $_SESSION['ryokin'] = 1; // 料金情報
        header("Location: 7_ErrorHikaiin.php"); // ログインページにリダイレクト
        exit;
    }

    // 座席番号とスケジュールIDを取得
    $seat_no = isset($_SESSION['seat_no']) ? $_SESSION['seat_no'] : null;
    $schedule_id = isset($_SESSION['schedule_id']) ? $_SESSION['schedule_id'] : null;

    // JECカード情報を取得
    $jeccardDAO = new JECcardDAO();
    $jeccard = $jeccardDAO->getJECcard($kno); // 会員IDを使ってJECカード情報を取得

    // PaymentDAO、Seat_yoyakuDAO、SeatDAOのインスタンス作成
    $paymentDAO = new PaymentDAO();
    $seatYoyakuDAO = new Seat_yoyakuDAO();
    $seatDAO = new SeatDAO();

    // 購入済みフラグがあればエラーページにリダイレクト
    if (isset($_SESSION['purchase_complete']) && $_SESSION['purchase_complete']) {
        header("Location: 7_ErrorKessai.php?error=alreadyPurchased");
        exit;
    }

    // 予約状況を更新
    if (isset($_POST['check'])) {
        $result = $paymentDAO->Payment($kno, $price); // 支払い処理

        if ($result) {
            // 予約状況の更新
            if ($seat_no && $schedule_id) {
                foreach ($seat_no as $seat_number) {
                    $seat_id = $seatDAO->get_seat_id($seat_number, $sno); // 座席ID取得
                    $updateResult = $seatYoyakuDAO->get_yoyaku($seat_id, $schedule_id); // 座席予約更新
                }
            }
            header("Location: 8_yoyakuEnd.php"); // 予約完了ページへ遷移
            exit;
        }
    }
} else {
    $_SESSION['purchase_complete'] = false; // 購入済みフラグをリセット
    header("Location: 7_ErrorKessai.php"); // エラーページに遷移
    exit;
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>決済確認</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/Kessai.css"> <!-- 外部CSSをリンク -->

    <script>
        // 残高不足の確認と、チャージ画面への遷移
        function checkBalance() {
            var price = <?php echo $price; ?>;
            var balance = <?php echo isset($jeccard->zandaka) ? $jeccard->zandaka : 0; ?>;

            if (price > balance) {
                var confirmMessage = '残高が不足しています。チャージ画面に遷移しますか？';
                if (confirm(confirmMessage)) {
                    <?php $_SESSION['kessai'] = 1;?> // チャージ画面用フラグを立てる
                    window.location.href = '9_2JECcard.php';  // チャージ画面へ遷移
                    return false; // フォーム送信を中止
                } else {
                    return false; // キャンセルなら送信中止
                }
            }
            return true; // 残高が足りている場合、送信続行
        }

        // ページ読み込み時に購入済みフラグを確認しアラート表示
        window.onload = function() {
            <?php if (isset($_SESSION['purchase_complete']) && $_SESSION['purchase_complete']): ?>
                alert('すでに購入済みです'); // すでに購入済みの場合のアラート
            <?php endif; ?>
        };

        // 戻るボタンの処理
        function goBack() {
            window.history.back(); // 前のページに戻る
        }
    </script>
</head>
<body>

<?php include "header2.php"; ?> <!-- ヘッダーの読み込み -->
<div class="container">
    <h1>決済確認</h1>
    <h3>決済金額：￥<?php echo htmlspecialchars($price); ?></h3> <!-- 決済金額表示 -->
    <h3>決済方法：日電カード</h3>
    <h3>現在の残高：￥<?php echo htmlspecialchars($jeccard->zandaka ?? '残高情報が取得できませんでした。'); ?></h3> <!-- 残高表示 -->

    <form action="" method="POST" onsubmit="return checkBalance()">
        <input type="submit" name="check" class="check" value="確認">
        <input type="button" name="check" class="check" onclick="goBack()" value="戻る">
        <input type="hidden" name="seat_no" value="<?php echo htmlspecialchars($seat_no); ?>">
        <input type="hidden" name="schedule_id" value="<?php echo htmlspecialchars($schedule_id); ?>">
        <input type="hidden" name="totalPrice" value="<?php echo htmlspecialchars($price); ?>">
    </form>
</div>
<footer class="footer">
    <p>© 2024 JecShopping</p>
</footer>

<!-- 必要なBootstrapのJavaScript -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
