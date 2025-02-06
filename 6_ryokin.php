<?php
require 'helpers/MovieDAO.php';
require_once 'helpers/KaiinDAO.php';
require 'helpers/seatDAO.php';
session_start();

// セッションから会員情報を取得
$kaiin = $_SESSION['kaiin'] ?? null;
$age = $_SESSION['kaiin']->age;

// 座席情報がPOSTされていれば、セッションに保存
if(isset($_POST['selectedSeats'])){
    $seat = $_POST['selectedSeats'];
    $_SESSION['hozonzaseki'] = $seat;
}else{
    $seat = $_SESSION['hozonzaseki'];
}

// 会員情報がない場合はエラーページに遷移
if($kaiin == null){
    $_SESSION['ryokin'] = 1;
    header("Location: 7_ErrorHikaiin.php");
    exit;
}

// MovieDAOのインスタンスを作成
$movieDAO = new MovieDAO();

// mcodeを取得
$mcode = $_SESSION['movie']->mcode; 

// レイティングを審査
$ratingCheck = $movieDAO->review_rating($age, $mcode);
if (!$ratingCheck) {
    header("Location: 6_ErrorRating.php");
    exit;
}

// セッションから映画情報とスケジュール情報を取得
$movie = $_SESSION['movie'] ?? null; 
$playdate = $_SESSION['playdate'];
$schedule = $_SESSION['schedule'] ?? [];
$time = $_SESSION['selected_time'];

// セッションから座席番号を取得
$sno = $_SESSION['selected_sno'];
if(isset($movie))
{

    $seatNumber = $_GET['seatnumber'] ?? null;
    $array = explode(",", $seat); // カンマで文字列を分割
    $_SESSION['seat'] = isset($seat) ? $seat : null;
    $sno = $_SESSION['selected_sno'];

    // セッションに座席番号を保存
    $_SESSION['seat_no'] = $array; // 配列形式で座席番号を格納

    // チケット料金一覧
    $ticketPrices = [
        '一般' => 2000,
        '大学・専門' => 1500,
        '高校生' => 1000,
        '中学・小学' => 1000,
        '幼児(3才以上)' => 1000,
        'シニア(60才以上)' => 1300,
        '障害者割引(付添1名まで)' => 1000
    ];

    // SeatDAOを使用してプレミアムシートの数を取得
    $seatDAO = new SeatDAO();
    $premium = $seatDAO->get_seat_premium($array, $sno); // 配列として渡す
    // プレミアムシートが選ばれている場合は、プレミアム料金を追加
    if ($premium > 0) {
        $totalPrice = 1000 * $premium; // プレミアムシートが選ばれている場合の合計金額
    } else {
        $totalPrice = 0; // プレミアムシートが選ばれていない場合の合計金額
    }
}else{
    if(!empty($_SESSION['kaiin'])){
        $kaiin = $_SESSION['kaiin'];
    } else {
        header("Location: index.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>チケット選択</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="css/Ryokin.css"> <!-- Ryokin.cssを読み込む -->
    <style>
        .ticket-option {
            cursor: pointer;
            padding: 10px 20px;
            text-align: center;
            border: 1px solid #ddd;
            transition: background-color 0.3s ease;
            display: inline-block;
            margin: 5px;
            border-radius: 5px;
        }

        .ticket-option:hover {
            background-color: #f0f0f0;
        }

        .ticket-option.selected {
            background-color: #4CAF50; /* 選択されたときの色 */
            color: white;
        }
    </style>
</head>
<body>
<?php
    // ヘッダーの表示 (会員かどうかで分岐)
    if (isset($_SESSION['kaiin'])) {
        include "header2.php";
    } else {
        include "header.php";
    }
?>
<h1>購入内容</h1>
<div class="info">映画名: <?=$movie->mname ?></div>
<div class="info"><strong>日付:</strong><?=$playdate ?><br>
<strong>上映時間:</strong><?=$time ?>

<div class="info">
    <?php foreach ($array as $index => $s): ?> 
        <?php 
            // プレミアムシートかどうかを確認
            $pmark = $seatDAO->get_premium($s, $sno); 
        ?>
        <strong>席番号:</strong> <?= $s ?>
        <?php if ($pmark === true): ?> <!-- プレミアムシートなら表示 -->
            <font color="yellow">プレミアムシート</font>
        <?php endif; ?>
        <input type="button" class="modal-trigger" data-bs-toggle="modal" data-bs-target="#ticketModal" data-seat="<?= $index ?>" value="チケット選択">
        <div class="info"><strong>選択したチケット:</strong> <span id="selectedTicket_<?= $index ?>" ></span></div>
        <?php if ($pmark === true): ?> <!-- プレミアムシートなら追加料金表示 -->
            <div class="info"><strong>選択した価格:</strong> <span id="selectedPrice_<?= $index ?>"></span><font color="yellow"> + 1000</font></span></div>
        <?php else: ?>
            <div class="info"><strong>選択した価格:</strong> <span id="selectedPrice_<?= $index ?>"></span></div>
        <?php endif; ?>
        <br>
    <?php endforeach; ?>
</div>

<div class="button-container">
    <div class="info"><strong>合計:</strong> <span id="totalPrice">￥ <?= number_format($totalPrice) ?></span></div>
    <form id="ticketForm" action="7_kessai.php" method="POST"> <!-- 現在のページをPOST -->
    <input type="hidden" id="totalPriceInput" name="totalPrice" value="<?= $totalPrice ?>">
    <button type="submit" class="next-button" id="nextButton" disabled>次へ</button>
</form>
    <button class="back-button" onclick="goBack()">戻る</button>
</div>

<!-- チケット選択用モーダル -->
<div class="modal fade" id="ticketModal" tabindex="-1" aria-labelledby="ticketModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ticketModalLabel">チケットを選択</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table">
                    <tr>
                        <td class="ticket-option" data-price="2000"><font color=white>一般</font></td>
                        <td class="ticket-option" data-price="1500"><font color=white>大学・専門</font></td>
                        <td class="ticket-option" data-price="1000"><font color=white>高校生</font></td>
                        <td class="ticket-option" data-price="1000"><font color=white>中学・小学</font></td>
                        <td class="ticket-option" data-price="1000"><font color=white>幼児(3才以上)</font></td>
                        <td class="ticket-option" data-price="1300"><font color=white>シニア(60才以上)</font></td>
                        <td class="ticket-option" data-price="1000"><font color=white>障害者割引(付添1名まで)</font></td>
                    </tr>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">閉じる</button>
                <button type="button" class="btn btn-primary" id="saveSelection">選択</button>
            </div>
        </div>
    </div>
</div>

<footer><p>© 2024 JecShopping</p></footer>

<!-- 必要なBootstrapのJavaScript -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<script>
   $(document).ready(function() {
    let selectedTicket = '';
    let selectedPrice = 0; // 数値として設定
    let selectedSeat = null; // 現在選択している座席番号
    let totalPrice = parseInt($('#totalPriceInput').val(), 10) || 0; // 合計金額を初期化

    // チケットオプションを選択した場合の処理
    $('.ticket-option').on('click', function() {
        $('.ticket-option').removeClass('selected');
        $(this).addClass('selected');
        selectedTicket = $(this).text(); // チケット名
        selectedPrice = $(this).data('price'); // チケット価格（数値）
    });

    // チケット選択時の保存処理
    $('#saveSelection').click(function() {
        if (selectedSeat !== null) {
            let currentSelected = $('#selectedTicket_' + selectedSeat).text();
            let currentPrice = 0;

            // 既に選択されている場合は更新
            if (currentSelected !== '') {
                currentPrice = parseInt($('#selectedPrice_' + selectedSeat).text().replace('￥ ', '').replace(/,/g, ''), 10);
            }

            if (currentSelected === '') {
                // チケットが未選択の場合、選択内容を反映
                $('#selectedTicket_' + selectedSeat).text(selectedTicket);
                $('#selectedPrice_' + selectedSeat).text('￥ ' + selectedPrice.toLocaleString());
                totalPrice += selectedPrice;
            } else {
                // 既存の選択内容を更新
                totalPrice = totalPrice - currentPrice + selectedPrice;
                $('#selectedTicket_' + selectedSeat).text(selectedTicket);
                $('#selectedPrice_' + selectedSeat).text('￥ ' + selectedPrice.toLocaleString());
            }

            // 合計金額を更新
            $('#totalPrice').text('￥ ' + totalPrice.toLocaleString());
            $('#totalPriceInput').val(totalPrice);

            // モーダルを閉じる
            $('#ticketModal').modal('hide');
            checkIfTicketsSelected();
        }
    });

    // モーダルを開く時、選択されている座席を設定
    $('.modal-trigger').on('click', function() {
        selectedSeat = $(this).data('seat');
    });

    // チケットがすべて選択されたか確認
    function checkIfTicketsSelected() {
        let allSelected = true;
        $('span[id^="selectedTicket_"]').each(function() {
            if ($(this).text() === '') {
                allSelected = false;
            }
        });
        // すべて選択された場合、次へ進むボタンを有効化
        if (allSelected) {
            $('#nextButton').prop('disabled', false);
        } else {
            $('#nextButton').prop('disabled', true);
        }
    }
    
    });

    // 戻るボタンの処理
    function goBack() {
        window.history.back();
    }
</script>
</body>
</html>
