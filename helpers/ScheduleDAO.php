<?php
require_once 'DAO.php';  // データベース接続のDAOクラスを読み込む

// 映画のスケジュールに関連する情報を保持するScheduleクラス
class Schedule 
{
    public int $mcode;              // 映画コード
    public ?DateTime $playdate = null;  // 上映日（null 許可）
    public int $sno;                // スクリーンナンバー
    public ?DateTime $starttime = null; // 開始時刻（null 許可）
    public ?DateTime $endtime = null;   // 終了時間（null 許可）
    public ?int $schedule_id = null;    // スケジュール ID（null 許可）
}

class ScheduleDAO
{
    // 指定した映画コードに関連するスケジュールを取得するメソッド
    public function get_schedule(int $mcode)
    {
        $dbh = DAO::get_db_connect();  // データベース接続を取得

        // 映画コード（mcode）と上映日が今日以降のスケジュールを取得するSQL
        $sql = "SELECT * FROM schedule WHERE mcode = :mcode AND playdate > GETDATE() ORDER BY playdate ASC";
        $stmt = $dbh->prepare($sql);

        // 映画コードをバインド
        $stmt->bindValue(':mcode', $mcode, PDO::PARAM_INT);

        // SQL実行
        $stmt->execute();

        // 結果をすべて取得して返す（複数行を取得）
        $schedule = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 結果があれば返す、なければ空の配列を返す
        return $schedule ?: [];
    }

    // 指定した映画に関連する上映日を取得するメソッド
    public function get_date(int $mcode)
    {
        try {
            $dbh = DAO::get_db_connect();  // データベース接続を取得

            // 映画コードに関連する上映日のみを取得するSQL
            $sql = "SELECT DISTINCT playdate FROM schedule WHERE mcode = :mcode AND playdate > GETDATE()";
            $stmt = $dbh->prepare($sql);

            // 映画コードをバインド
            $stmt->bindValue(':mcode', $mcode, PDO::PARAM_INT);

            // SQL実行
            $stmt->execute();

            // 結果をすべて取得して返す（複数行を取得）
            $schedule = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 結果があれば返す、なければ空の配列を返す
            return $schedule ?: [];

        } catch (PDOException $e) {
            // データベース接続エラーが発生した場合、エラーメッセージを表示
            echo "データベースエラー: " . $e->getMessage();
            return [];  // エラー時は空の配列を返す
        }
    }

    // 新しいスケジュールをデータベースに挿入するメソッド
    public function insert_schedule($schedule)
    {
        $dbh = DAO::get_db_connect();  // データベース接続を取得

        // スケジュールを挿入するSQL文
        $sql = "INSERT INTO schedule (mcode, playdate, sno, starttime, endtime)
                VALUES (:mcode, :playdate, :sno, :starttime, :endtime)";

        $stmt = $dbh->prepare($sql);

        // 各パラメータをバインド
        $stmt->bindValue(':mcode', $schedule->mcode, PDO::PARAM_INT);
        $stmt->bindValue(':playdate', $schedule->playdate ? $schedule->playdate->format('Y-m-d') : null, PDO::PARAM_STR);
        $stmt->bindValue(':sno', $schedule->sno, PDO::PARAM_INT);
        $stmt->bindValue(':starttime', $schedule->starttime ? $schedule->starttime->format('Y-m-d H:i:s') : null, PDO::PARAM_STR);
        $stmt->bindValue(':endtime', $schedule->endtime ? $schedule->endtime->format('Y-m-d H:i:s') : null, PDO::PARAM_STR);

        // SQL実行
        $stmt->execute();
    }

    // 映画コードに基づいてすべてのスケジュールを取得するメソッド
    public function get_schedule_by_mcode($mcode) 
    {
        $dbh = DAO::get_db_connect();  // データベース接続を取得

        // 映画コードに基づいてスケジュールを取得するSQL
        $sql = "SELECT * FROM schedule WHERE mcode = :mcode";
        $stmt = $dbh->prepare($sql);

        // 映画コードをバインド
        $stmt->bindValue(':mcode', $mcode, PDO::PARAM_INT);

        // SQL実行
        $stmt->execute();

        $schedules = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // 新しい Schedule オブジェクトを作成
            $schedule = new Schedule();

            // プロパティにデータをセット（DateTime 型に変換）
            $schedule->mcode = $row['mcode'];
            $schedule->sno = $row['sno'];
            $schedule->schedule_id = $row['schedule_id'];
            
            // 日付型のプロパティを変換してセット
            $schedule->playdate = !empty($row['playdate']) ? new DateTime($row['playdate']) : null;
            $schedule->starttime = !empty($row['starttime']) ? new DateTime($row['starttime']) : null;
            $schedule->endtime = !empty($row['endtime']) ? new DateTime($row['endtime']) : null;

            // スケジュールオブジェクトを配列に追加
            $schedules[] = $schedule;
        }

        // すべてのスケジュールを返す
        return $schedules;
    }

    // 指定したスケジュールIDに基づいてスケジュールを削除するメソッド
    public function delete_schedule($schedule_id) 
    {
        $dbh = DAO::get_db_connect();  // データベース接続を取得
        
        // スケジュールIDを基に削除するSQL
        $sql = "DELETE FROM schedule WHERE schedule_id = :schedule_id";
        $stmt = $dbh->prepare($sql);

        // スケジュールIDをバインド
        $stmt->bindParam(':schedule_id', $schedule_id, PDO::PARAM_INT);

        // SQL実行
        if ($stmt->execute()) {
            echo "スケジュールID {$schedule_id} を削除しました。";  // 成功メッセージ
            return true;  // 成功
        } else {
            echo "スケジュールID {$schedule_id} の削除に失敗しました。";  // 失敗メッセージ
            return false;  // 失敗
        }
    }

    // スケジュール情報を基に、スケジュールIDを取得するメソッド
    public function get_schedule_id($mcode, $selected_screen, $playdate, $starttime, $endtime) {
        $dbh = DAO::get_db_connect();  // データベース接続を取得

        // 指定された映画コード、スクリーン、日時に一致するスケジュールIDを取得するSQL
        $sql_schedule = "SELECT schedule_id 
                         FROM schedule 
                         WHERE mcode = :mcode 
                           AND playdate = :playdate 
                           AND starttime = :starttime 
                           AND endtime = :endtime 
                           AND sno = :sno";

        $stmt_schedule = $dbh->prepare($sql_schedule);
        $stmt_schedule->bindParam(':mcode', $mcode, PDO::PARAM_INT);
        $stmt_schedule->bindParam(':playdate', $playdate, PDO::PARAM_STR);
        $stmt_schedule->bindParam(':starttime', $starttime, PDO::PARAM_STR);
        $stmt_schedule->bindParam(':endtime', $endtime, PDO::PARAM_STR);
        $stmt_schedule->bindParam(':sno', $selected_screen, PDO::PARAM_INT);

        // SQL実行
        $stmt_schedule->execute();

        // スケジュール情報を取得
        $schedule = $stmt_schedule->fetch(PDO::FETCH_ASSOC);

        // スケジュールIDが見つかれば返す
        if ($schedule && isset($schedule['schedule_id'])) {
            return $schedule['schedule_id'];
        }

        // 見つからない場合はnullを返す
        return null;
    }
}
?>
