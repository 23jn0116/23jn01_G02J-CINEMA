<?php
session_start();
$_SESSION['purchase_complete'] = false;

// 必要なDAOファイルをインクルード
require_once './helpers/MovieDAO.php';
require_once './helpers/AreaDAO.php';
require_once './helpers/ScreenDAO.php';
require_once './helpers/SeatDAO.php';
require_once './helpers/ScheduleDAO.php';

// 映画コードがURLパラメータに存在する場合
if (isset($_GET['mcode'])) {
    $mcode = $_GET['mcode']; 
}

$movie = null;
$schedule = [];
$screen = [];
$ccode = null;
$availableTickets = null;
$playdate = isset($_GET['playdate']) ? $_GET['playdate'] : null;
$_SESSION['playdate'] = $playdate;
$area = isset($_POST['areaname']) ? $_POST['areaname'] : null;
$age = $_SESSION['age'] ?? null;  // ユーザーの年齢をセッションから取得

// 映画コードが正しい場合、映画情報を取得
if ($mcode !== null && $mcode > 0) {
    $movieDAO = new MovieDAO();
    $scheduleDAO = new ScheduleDAO();
    $movie = $movieDAO->get_mcode_movie($mcode);
    if ($movie) {
        $schedule = $scheduleDAO->get_schedule($mcode);
        $_SESSION['movie'] = $movie;  // 映画情報をセッションに保存
        $_SESSION['schedule'] = $schedule;  // 上映スケジュールをセッションに保存
    } else {
        echo "<p>映画情報が見つかりませんでした。</p>";
    }
} else {
    echo "<p>無効な映画コードです。</p>";
}

// GETリクエストの場合、上映日やスクリーン情報を取得
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['playdate'])) {
        $playdate = $_GET['playdate']; 
    }

    // 上映日が選択されていれば、スクリーン番号を取得
    if (isset($playdate)) {
        $screenDAO = new ScreenDAO();
        $screen_numbers = $screenDAO->get_sno($playdate);
        if (!empty($screen_numbers)) {
            sort($screen_numbers);  // スクリーン番号をソート
            $screen_numbers = array_unique($screen_numbers);  // 重複を削除
        }
    }

    // 上映日が選ばれている場合、スクリーンごとの上映時間を取得
    if (isset($playdate)) {
        $screenDAO = new ScreenDAO();
        $screen = [];

        // 各スクリーンに対応する上映時間を取得
        foreach ($screen_numbers as $screen_number) {
            $screentimes = $screenDAO->get_screentime($screen_number, $mcode, $playdate);
            if (is_array($screentimes) && !empty($screentimes)) {
                foreach ($screentimes as $time) {
                    $starttime = new DateTime($time['starttime']);
                    $endtime = new DateTime($time['endtime']);
                    $timeKey = $starttime->format('H:i') . '-' . $endtime->format('H:i');

                    // スクリーン番号ごとの上映時間情報を格納
                    if (!isset($screen[$screen_number])) {
                        $screen[$screen_number] = [];
                    }

                    // 20:00以降はレイトショーと判定
                    $isLateShow = $starttime->format('H:i') >= '20:00';
                    
                    $screen[$screen_number][] = [
                        'starttime' => $starttime->format('Y-m-d H:i:s'),
                        'endtime' => $endtime->format('Y-m-d H:i:s'),
                        'timeKey' => $timeKey,
                        'isLateShow' => $isLateShow
                    ];
                }
            }
        }

        // スクリーン番号に基づいてエリア情報を取得
        $areaDAO = new AreaDAO();
        $ccode = $areaDAO->get_ccode_by_sno($screen_numbers[0]);
        $seatDAO = new SeatDAO();  // 座席情報を取得するためのDAO
    }

    // 上映時間が選択された場合、対応する時間帯をセッションに保存
    if (isset($_GET['selected_time'])) {
        $selected_time = $_GET['selected_time']; // 例: "09:00 - 11:00"
        list($starttime, $endtime) = explode(' - ', $selected_time);
        
        $selected_starttime = new DateTime($playdate . ' ' . $starttime);
        $selected_endtime = new DateTime($playdate . ' ' . $endtime);
        
        $_SESSION['starttime'] = $selected_starttime;
        $_SESSION['endtime'] = $selected_endtime;
        $_SESSION['selected_time'] = $selected_time;  // 時間帯をそのまま保存
        $_SESSION['selected_screen'] = $screen_number;
     
        // 座席情報を取得
        if ($ccode !== null) {
            $availableTickets = $seatDAO->get_available_tickets(
                $mcode, 
                $ccode, 
                $selected_starttime->format('Y-m-d H:i:s'), 
                $selected_endtime->format('Y-m-d H:i:s'), 
                $_GET['selected_screen']
            );
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>日程・日時検索</title>
    <link href="./css/style.css" rel="stylesheet">
</head>
<body>
    <?php
        if (isset($_SESSION['kaiin'])) {
            include "header2.php";  // ログインユーザー向けのヘッダー
        } else {
            include "header.php";  // 未ログインユーザー向けのヘッダー
        }
    ?>
    <h1><?= htmlspecialchars($movie->mname ?? '映画名不明') ?></h1>
    <?php if ($movie): ?>
        <div style="text-align: center;">
            <img src="../images/moviephoto/<?= htmlspecialchars($movie->photo) ?>" alt="<?= htmlspecialchars($movie->mname) ?>" width="250" height="auto">
            <p><?= htmlspecialchars($movie->detail) ?></p>
        </div>
    <?php else: ?>
        <p>画像が見つかりませんでした。</p>
    <?php endif; ?>
    
    <div class="button-container">
        <form action="" method="GET">
            <input type="hidden" name="mcode" value="<?= htmlspecialchars($mcode) ?>">
            <table>
                <tr>
                    <?php
                    // 上映スケジュールの中から最初の3つの上映日をボタンとして表示
                    if (!empty($schedule)) {
                        $playdates = array_column($schedule, 'playdate');
                        $uniquePlaydates = array_unique($playdates);
                        $firstThreeUniquePlaydates = array_slice($uniquePlaydates, 0, 3);

                        foreach ($firstThreeUniquePlaydates as $playdate_option) {
                            echo "<td><button type='submit' class='movie-button' name='playdate' value='" . htmlspecialchars($playdate_option) . "'>" . htmlspecialchars($playdate_option) . "</button></td>";
                        }
                    } else {
                        echo "<p>上映日が見つかりませんでした。</p>";
                    }
                    ?>
                </tr>
            </table>
        </form>
    </div>
    
    <?php if (!empty($screen) && isset($playdate)): ?>
        <form action="5_zaseki.php" method="GET">
            <input type="hidden" name="mcode" value="<?= htmlspecialchars($mcode) ?>">
            <input type="hidden" name="playdate" value="<?= htmlspecialchars($playdate) ?>">
            <table>
                <?php foreach ($screen as $screen_number => $times): ?>
                    <tr>
                        <td>
                            <div class="note">
                                <h2>スクリーン <?= htmlspecialchars($screen_number) ?></h2>
                                <div>
                                    <?php foreach ($times as $time): ?>
                                        <?php
                                            $starttime = date('H:i', strtotime($time['starttime']));
                                            $endtime = date('H:i', strtotime($time['endtime']));
                                            $label = "$starttime - $endtime";
                                            
                                            // レイトショーが含まれている場合、改行を挿入
                                            if ($time['isLateShow']) {
                                                $label = "レイトショー\n$starttime - $endtime";  // レイトショーの前に改行を追加
                                            }
                                          
                                            // 18歳未満の場合、レイトショーのボタンを無効化
                                            $disabled = ($time['isLateShow'] && $age < 18) ? 'disabled' : '';
                                        ?>
                                        <!-- ボタンの改行を防止するためにstyleを調整 -->
                                        <form action="5_zaseki.php" method="GET" style="display: inline-block; margin: 10px;">
                                            <input type="hidden" name="mcode" value="<?= htmlspecialchars($mcode) ?>">
                                            <input type="hidden" name="playdate" value="<?= htmlspecialchars($playdate) ?>">
                                            <input type="hidden" name="selected_screen" value="<?= htmlspecialchars($screen_number) ?>">
                                            <input type="hidden" name="selected_time" value="<?= htmlspecialchars($label) ?>">
                                            <button type="submit" class="no-wrap-button" name="selected_time" value="<?= htmlspecialchars($label) ?>" <?= $disabled ?>><br>
                                                <?= nl2br(htmlspecialchars($label)) ?> <!-- 改行をHTMLで反映 --><br><br>
                                            </button>
                                        </form>
                                    <?php endforeach; ?>
                                </div>
                            </div>    
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </form>
    <?php elseif (isset($playdate)): ?>
        <p>有効な上映時間がありません。</p>
    <?php endif; ?>
</body>
</html>
