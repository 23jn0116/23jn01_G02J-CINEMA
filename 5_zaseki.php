<?php
// 必要なDAOファイルをインクルード
require './helpers/MovieDAO.php';
require './helpers/SeatDAO.php';
require './helpers/ScheduleDAO.php';
require './helpers/Seat_yoyakuDAO.php';

// セッションが開始されていない場合に開始する
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    $_SESSION['purchase_complete'] = false;
}

// GET パラメータの取得と検証
$movie_code = isset($_GET['mcode']) ? $_GET['mcode'] : null;  // 映画コード
$selected_screen = isset($_GET['selected_screen']) ? $_GET['selected_screen'] : null;  // 選択されたスクリーン
$playdate = isset($_GET['playdate']) ? $_GET['playdate'] : null;  // 公開日
$selected_time = isset($_GET['selected_time']) ? $_GET['selected_time'] : null;  // 選択された上映時間
$_SESSION['selected_time'] = $selected_time;
$movie = $_SESSION['movie'] ?? null;  // 映画の詳細情報
$sno = isset($_GET['selected_screen']) ? $_GET['selected_screen'] : null;  // スクリーン番号
$_SESSION['selected_sno'] = $sno;

// 時間のフォーマットと検証
if ($selected_time) {
    list($starttime, $endtime) = explode('-', $selected_time);  // 開始時間と終了時間を分ける
    $starttime = str_replace("レイトショー", "", $starttime);  //レイトショーの場合レイトショーの文字を取り除く
    $starttime = str_replace("\r\n", "", $starttime);  //レイトショーの場合レイトショーの文字を取り除く
    $start_datetime = $playdate . ' ' . $starttime;  // 開始日時
    $end_datetime = $playdate . ' ' . $endtime;  // 終了日時
} else {
    $start_datetime = $end_datetime = null;  // 時間が選択されていない場合
}

// DAOオブジェクトの作成
$seatDAO = new SeatDAO();
$scheduleDAO = new ScheduleDAO();
$seat_yoyakuDAO = new Seat_yoyakuDAO();

// データ取得と処理
if ($movie_code && $selected_screen && $playdate) {
    // 座席情報と予約情報を取得
    $seats = $seatDAO->get_seat_deta($movie_code, $selected_screen, $playdate, $start_datetime, $end_datetime);
    $seat_yoyaku = $seat_yoyakuDAO->get_seat_yoyaku($movie_code, $selected_screen, $playdate, $start_datetime, $end_datetime);
    $schedule_id = $scheduleDAO->get_schedule_id($movie_code, $selected_screen, $playdate, $start_datetime, $end_datetime);

    // スケジュールIDをセッションに保存
    $_SESSION['schedule_id'] = $schedule_id;

    if ($seats) {
        // 座席データの整形と予約状態の処理
        $seats_by_column = [];
        $processedSeats = [];
        $yoyaku_map = [];
        foreach ($seat_yoyaku as $yoyaku) {
            $yoyaku_map[$yoyaku->seatid] = $yoyaku->yoyaku;  // 予約情報をマップ化
        }
        
        foreach ($seats as $seatData) {
            if ($seatData->seatno !== null) {
                preg_match('/([A-Z]+)(\d+)/', $seatData->seatno, $matches);  // 座席番号を分割
                $column = $matches[1];  // 列
                $seatNumber = $matches[2];  // 番号

                if (in_array($seatData->seatno, $processedSeats)) {
                    continue;  // すでに処理した座席はスキップ
                }
                $processedSeats[] = $seatData->seatno;  // 処理した座席リストに追加

                if (!isset($seats_by_column[$column])) {
                    $seats_by_column[$column] = [];  // 列ごとの座席を格納
                }

                // 予約状況を取得
                $yoyaku_status = isset($yoyaku_map[$seatData->seatno]) ? $yoyaku_map[$seatData->seatno] : null;
                $seats_by_column[$column][] = [
                    'seatno' => $seatData->seatno,
                    'premium' => $seatData->premium,  // プレミアムシート情報
                    'yoyaku' => $yoyaku_status  // 予約状態
                ];
            }
        }
    } else {
        echo "<p>座席情報が見つかりません。</p>";  // 座席情報がない場合
        exit;
    }
} else {
    echo "<p>無効な座席番号です。</p>";  // 必要なパラメータが不足している場合
    exit;
}
?>

<!-- HTML 部分 -->
<script>
    let selectedSeats = [];  // 選択された座席の配列
    let totalPrice = 0;  // 合計金額の初期化
    const premiumSeatPrice = 1000;  // プレミアムシートの追加料金

    // 座席の選択と解除を切り替える関数
    function toggleSeat(seat) {
        const seatElement = document.getElementById(seat);

        // 座席がすでに予約されている場合、何も変更しない
        if (seatElement.classList.contains('occupied') || seatElement.classList.contains('occupied-premium')) {
            alert('この座席はすでに予約されています。');
            return;
        }

        // 最大4席まで選択可能
        if (selectedSeats.length >= 4 && !selectedSeats.includes(seat)) {
            alert('最大4席まで選択できます。');
            return;
        }

        // 座席が選択済みかどうかの状態を取得
        const index = selectedSeats.indexOf(seat);
        if (index > -1) {  // すでに選択されている場合、解除
            selectedSeats.splice(index, 1);
            seatElement.classList.remove('selected');

            // プレミアムシートなら金額から1000円を引く
            if (seatElement.classList.contains('premium')) {
                totalPrice -= premiumSeatPrice;
            }
        } else {  // 未選択の場合、選択する
            selectedSeats.push(seat);
            seatElement.classList.add('selected');

            // プレミアムシートなら金額に1000円を加算
            if (seatElement.classList.contains('premium')) {
                totalPrice += premiumSeatPrice;
            }
        }

        // 選択された座席の情報を表示
        document.getElementById('selectedSeatsInput').value = selectedSeats.join(',');
        const selectedSeatsDisplay = document.getElementById('selectedSeatsDisplay');
        selectedSeatsDisplay.textContent = selectedSeats.length > 0 ? '選択した座席: ' + selectedSeats.join(', ') : '選択した座席: なし';
    }

    // 座席選択を検証する関数
    function validateSeats() {
        if (selectedSeats.length === 0) {
            alert('座席を選択してください。');
            return false;  // 座席が選択されていなければ送信を防止
        }
        return true;  // 座席が選択されていれば送信を許可
    }

    // 戻るボタンを押したときに前のページに戻る関数
    function goBack() {
        window.history.back();
    }
</script>

<!DOCTYPE html>
<html lang="ja">
<head>
    <?php if (isset($_SESSION['kaiin'])) {
        include "header2.php";  // 会員用ヘッダー
    } else {
        include "header.php";  // 一般用ヘッダー
    } ?>
    <meta charset="utf-8">
    <title>映画館の座席予約</title>
    <link href="./css/seatstyle.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <!-- 左側：座席の説明 -->
        <div class="left-column">
            <h2>座席の説明</h2>
            映画名: <?= $movie->mname ?><br><br>
            日時: <?= $playdate ?><br>
            <?= $selected_time ?><br>
            スクリーン番号:<?= $sno ?>
            <ul>
                <li><span class="seat occupied"></span>: 予約された座席</li>
                <li><span class="seat unreserved"></span>: 空いている座席</li>
                <li><span class="seat premium"></span>: プレミアム席<span style="color:red;">（+1000円）</span></li>
                <li><span class="seat selected"></span>: 選択された座席</li>
            </ul>
            <div id="selectedSeatsDisplay" style="color: red; margin-top: 20px;">選択した座席: なし</div>
        </div>

        <!-- 右側：座席選択 -->
        <div class="right-column">
            <div class="screen-container">
                <div class="screen-arch"></div>
                <h1>SCREEN <?php echo htmlspecialchars($seats[0]->sno, ENT_QUOTES, 'UTF-8'); ?></h1>
            </div>

            <div id="seats">
            <?php if (!empty($seats_by_column)): ?>
                <?php foreach ($seats_by_column as $column => $seats): ?>
                    <div class="column">
                        <h2><?php echo htmlspecialchars($column); ?></h2>
                        <div class="seats">
                            <?php foreach ($seats as $seatData): ?>
                                <?php
                                $seatClass = 'seat';
                                if ($seatData['premium']) {
                                    $seatClass .= ' premium'; // プレミアム席のクラスを追加
                                }
                                if ($seatData['yoyaku'] === 'unreserved') {
                                    $seatClass .= ' unreserved';  // 空席
                                } elseif ($seatData['yoyaku']) {
                                    if ($seatData['premium']) {
                                        $seatClass .= ' occupied-premium';  // プレミアム席の予約済み
                                    } else {
                                        $seatClass .= ' occupied';  // 通常の予約済み
                                    }
                                } 
                                ?>
                                <div class="<?= htmlspecialchars($seatClass) ?>" id="<?= htmlspecialchars($seatData['seatno']) ?>" onclick="toggleSeat('<?= htmlspecialchars($seatData['seatno']) ?>')">
                                    <p><?= htmlspecialchars($seatData['seatno']) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div>座席情報がありません。</div>
            <?php endif; ?>
            </div>

            <form action="6_ryokin.php" method="POST" onsubmit="return validateSeats()">
                <input type="hidden" id="selectedSeatsInput" name="selectedSeats" value="">
                <input type="hidden" id="selected_time" name="selected_time" value="<?php echo $selected_time; ?>">
                <button type="submit" name="reserveButton" class="reserve-button">予約する</button>
            </form>
            <button type="button" name="backButton" class="reserve-button" onclick="goBack()">戻る</button>
        </div>
    </div>
</body>
</html>
