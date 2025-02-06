<?php
require_once 'helpers/MovieDAO.php';
require_once 'helpers/KaiinDAO.php';
require_once 'helpers/yoyakulistDAO.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
    // セッションから会員情報、映画情報、スケジュール情報を取得
    $kaiin = $_SESSION['kaiin'];
    $kno = $kaiin->kno;
}

$yoyakulistDAO = new yoyakulistDAO();
$movieDAO = new movieDAO();

// 予約情報を取得
$yoyakulist = $yoyakulistDAO->get_yoyaku($kno);

// 予約情報が取得できない場合はエラーページにリダイレクト
if ($yoyakulist === null) {
    header("Location: 9_5Erroryoyaku.php");
    exit;
}

// キャンセル処理
if (isset($_POST['cancel'])) {
    $yoyaku_id = $_POST['yoyakuid'];  // キャンセルする予約IDを取得
    try {
        // 予約削除
        $yoyakulistDAO->delete_yoyaku($yoyaku_id);
        $_SESSION['cancel_message'] = "予約がキャンセルされました。"; // セッションにキャンセルメッセージを保存
    } catch (PDOException $e) {
        $_SESSION['cancel_message'] = "キャンセル処理中にエラーが発生しました。";
    }
    header("Location: 9_5yoyaku.php"); // 予約一覧ページにリダイレクト
    exit;
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>予約済み映画</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/yoyakuEnd.css">
</head>
<body>
<?php include "header2.php"; ?>

<div class="container">
    <h1>ご予約いただいた作品</h1>
    
    <!-- キャンセル完了メッセージ -->
    <?php if (isset($_SESSION['cancel_message'])): ?>
        <div class="alert alert-success" role="alert">
            <?php echo htmlspecialchars($_SESSION['cancel_message']); ?>
        </div>
        <?php unset($_SESSION['cancel_message']); ?> <!-- メッセージを表示後、セッションから削除 -->
    <?php endif; ?>
    
    <div class="reservation-details">
    <?php foreach ($yoyakulist as $yoyaku) { 
        // 映画情報を取得
        $mcode = $yoyaku['mcode']; // $yoyakuは連想配列なので、'mcode'でアクセス
        $movie = $movieDAO->get_mcode_movie($mcode);  // mcodeがint型であることを確認

        // 映画情報が取得できた場合
        $movieName = $movie->mname ?? '映画情報がありません'; // 映画名取得
        $playdate = $yoyaku['playdate'];  
        $time = $yoyaku['time'];  
        $areaname = $yoyaku['areaname'];
        $ticket = $yoyaku['ticket_type'] ?? '情報がありません';
    ?>
        <form action="9_5yoyakulist.php" method="POST" id="cancelForm-<?php echo $yoyaku['yoyaku_id']; ?>">
            <div class="note">
                <?php $yoyakuid = $yoyaku['yoyaku_id']; ?>
                <input type="hidden" name="yoyakuid" value="<?php echo htmlspecialchars($yoyakuid); ?>">
                映画名: <?php echo htmlspecialchars($movieName); ?><br>
                上映日: <?php echo htmlspecialchars($playdate); ?><br>
                上映時間: <?php echo htmlspecialchars($time); ?><br>
                場所: <?php echo htmlspecialchars($areaname); ?><br>

                <!-- 詳細を見るボタン -->
                <input type="submit" class="home-button" value="詳細を見る">

                <!-- キャンセルボタン -->
                <button type="button" class="btn btn-danger cancel-btn" data-yoyakuid="<?php echo $yoyakuid; ?>" data-bs-toggle="modal" data-bs-target="#confirmation-modal">
                    予約をキャンセル
                </button>
            </div>
        </form>
    <?php } ?>
    </div>

    <button class="home-button" onclick="location.href='1_1index2.php'">ホームへ戻る</button>
    <button class="home-button" onclick="location.href='9_1mypage.php'">戻る</button>
</div>

<!-- キャンセル確認モーダル -->
<div class="modal fade" id="confirmation-modal" tabindex="-1" aria-labelledby="confirmation-modal-label" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="confirmation-modal-label">予約キャンセルの確認</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        本当に予約をキャンセルしてもよろしいですか？
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
        <button type="button" class="btn btn-danger" id="confirm-cancel">キャンセルする</button>
      </div>
    </div>
  </div>
</div>

<script>
    let selectedYoyakuId = null;

    // キャンセルボタンがクリックされた時
    document.querySelectorAll('.cancel-btn').forEach(button => {
        button.addEventListener('click', function() {
            // クリックされた予約IDを取得
            selectedYoyakuId = this.getAttribute('data-yoyakuid');
        });
    });

    // モーダル内のキャンセル確認ボタンがクリックされた時
    document.getElementById('confirm-cancel').addEventListener('click', function() {
        if (selectedYoyakuId) {
            // キャンセルフォームを取得
            const form = document.getElementById('cancelForm-' + selectedYoyakuId);

            // フォームを送信
            form.submit();
        }
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
