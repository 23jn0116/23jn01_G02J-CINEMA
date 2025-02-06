<?php
require './helpers/MovieDAO.php';
require './helpers/ScheduleDAO.php';

$movieDAO = new MovieDAO();
$movie_list = $movieDAO->get_movie();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['tuika'])) {
        // フォームから送信されたデータを取得
        $mcode = $_POST['mcode'];
        $playdate = $_POST['playdate'];
        $sno = $_POST['sno'];
        $starttime = $_POST['starttime'];
        $endtime = $_POST['endtime'];

        // エラーチェック用の配列
        $errs = [];

        // バリデーション
        if (empty($playdate)) {
            $errs[] = '上映日は必須です。';
        } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $playdate)) {
            $errs[] = '上映日の日付形式が不正です。';
        }
        if (empty($sno)) {
            $errs[] = 'スクリーン番号は必須です。';
        }
        if (empty($starttime)) {
            $errs[] = 'スタート時間は必須です。';
        }
        if (empty($endtime)) {
            $errs[] = '終了時間は必須です。';
        } elseif ($endtime <= $starttime) {
            $errs[] = '終了時間はスタート時間より後でなければなりません。';
        }

        // エラーがなければスケジュールを追加
        if (empty($errs)) {
            try {
                $schedule = new Schedule();
                $scheduleDAO = new ScheduleDAO();

                // playdate を DateTime オブジェクトに変換
                if ($playdate instanceof DateTime) {
                    $playdateStr = $playdate->format('Y-m-d');
                } else {
                    $playdateStr = $playdate;
                }

                $schedule->mcode = $mcode;
                $schedule->playdate = new DateTime($playdateStr); // 日付のみの DateTime
                $schedule->sno = $sno;
                $schedule->starttime = new DateTime($playdateStr . ' ' . $starttime); // 日付 + 時間の DateTime
                $schedule->endtime = new DateTime($playdateStr . ' ' . $endtime); // 日付 + 時間の DateTime

                $scheduleDAO->insert_schedule($schedule); // データベースに挿入

                $_SESSION['message'] = 'スケジュールが追加されました。';
                header('Location: 11_5scheduleAdd.php');
                exit;
            } catch (Exception $e) {
                // DateTime の変換エラーをキャッチしてエラーメッセージを表示
                echo '<div class="alert alert-danger">日付または時間の形式が正しくありません。</div>';
            }
        } else {
            // エラーがある場合、エラーメッセージを表示
            foreach ($errs as $err) {
                echo '<div class="alert alert-danger">' . htmlspecialchars($err) . '</div>';
            }
        }
    } elseif (isset($_POST['deleteSchedule'])) {
        // 削除処理
        $mcode = $_POST['mcode'];
        
        if (!empty($mcode)) {
            $scheduleDAO = new ScheduleDAO();
            $schedules = $scheduleDAO->get_schedule_by_mcode($mcode);

            // 現在の日付を取得
            $current_date = new DateTime();
            $deleted = false; // 削除が行われたかを示すフラグ

            if ($schedules) {
                foreach ($schedules as $schedule) {
                    // playdate は DateTime オブジェクトとして取得されることを期待
                    $schedule_date = $schedule->playdate;

                    // 現在の日付よりも過去の日付であることを確認
                    if ($schedule_date < $current_date) {
                        $scheduleDAO->delete_schedule($schedule->schedule_id); // 正しいプロパティを使用
                        $deleted = true; // 削除処理を行った
                    }
                }

                if ($deleted) {
                    $_SESSION['message'] = '過去のスケジュールが削除されました。';
                } else {
                    $_SESSION['message'] = '削除する過去の日付のスケジュールはありませんでした。';
                }
            } else {
                $_SESSION['message'] = 'スケジュールが見つかりません。';
            }

            header('Location: 11_5scheduleAdd.php');
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <title>映画リスト</title>
</head>
<body>
    <div class="container">
        <!-- セッションメッセージ表示部分 -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-info"><?= $_SESSION['message'] ?></div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>
        <h1>映画リスト</h1>
        <div class="mb-3">
            <a href="10_1kanri.php" class="btn btn-secondary">戻る</a>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th>映画コード</th>
                    <th>映画名</th>
                    <th>写真</th>
                    <th>上映時間追加</th>
                    <th>削除</th>
                </tr>
            </thead>
            <tbody>
                <?php if (isset($movie_list) && count($movie_list) > 0): ?>
                    <?php foreach ($movie_list as $movie): ?>
                        <tr>
                            <td><?= $movie->mcode ?></td>
                            <td><?= $movie->mname ?></td>
                            <td>
                                <img src="../images/moviephoto/<?= $movie->photo ?>" alt="<?= $movie->mname ?>" style="width: 100px;">
                            </td>
                            <td>
                                <!-- 上映時間追加モーダル -->
                                <button class="btn btn-warning" data-toggle="modal" data-target="#changePhotoModal<?= $movie->mcode ?>">追加</button>
                            </td>
                            <td>
                                <!-- 削除フォーム -->
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="mcode" value="<?= $movie->mcode ?>">
                                    <button type="submit" name="deleteSchedule" class="btn btn-danger">削除</button>
                                </form>
                            </td>
                        </tr>

                        <!-- 上映時間追加モーダル -->
                        <div class="modal fade" id="changePhotoModal<?= $movie->mcode ?>" tabindex="-1" role="dialog" aria-labelledby="changePhotoModalLabel" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="changePhotoModalLabel">上映時間の追加</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <form id="movieForm" method="POST" enctype="multipart/form-data">
                                            <div class="form-group">
                                                <label for="mcode">映画コード</label>
                                                <input type="text" name="mcode" class="form-control" value="<?=$movie->mcode?>" readonly>
                                            </div>
                                            <div class="form-group">
                                                <label for="releaseYear">上映日</label>
                                                <input type="date" name="playdate" class="form-control" placeholder="上映日を入力" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="description">スクリーン番号</label>
                                                <input type="number" name="sno" class="form-control" placeholder="スクリーン番号を入力" required min=1>
                                            </div>
                                            <div class="form-group">
                                                <label for="description">スタート時間</label>
                                                <input type="time" name="starttime" class="form-control" placeholder="スタート時間" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="description">終了時間</label>
                                                <input type="time" name="endtime" class="form-control" placeholder="終了時間" required>
                                            </div>
                                            <button type="submit" name="tuika" class="btn btn-primary">追加</button> <button type="reset" class="btn btn-primary">リセット</button>
                                        </form>
                                        <p><a class="btn btn-primary" href="11_5scheduleAdd.php">戻る</a></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">データがありません</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
