<?php
require_once 'DAO.php';  // データベース接続のDAOクラスを読み込む

// 座席予約情報を保持するクラス
class Seat_yoyaku
{
    public int $yoyaku_id;       // 予約ID
    public string $seatid;       // 座席ID
    public int $schedule_id;     // スケジュールID
    public ?string $yoyaku;      // 予約の有無（null許容型に変更）
}

class Seat_yoyakuDAO {

    // schedule_id に基づいて座席の予約状況を取得するメソッド
    public function get_seat_yoyaku($mcode, $selected_screen, $playdate, $starttime, $endtime) {
        $dbh = DAO::get_db_connect();  // データベース接続を取得
    
        try {
            // スケジュールIDを取得するSQL文
            $sql_schedule = "SELECT schedule_id 
                             FROM schedule 
                             WHERE mcode = :mcode 
                               AND playdate = :playdate 
                               AND starttime = :starttime 
                               AND endtime = :endtime 
                               AND sno = :sno";
    
            $stmt_schedule = $dbh->prepare($sql_schedule);
    
            // パラメータをSQLクエリにバインド
            $stmt_schedule->bindParam(':mcode', $mcode, PDO::PARAM_INT);
            $stmt_schedule->bindParam(':playdate', $playdate, PDO::PARAM_STR);
            $stmt_schedule->bindParam(':starttime', $starttime, PDO::PARAM_STR);
            $stmt_schedule->bindParam(':endtime', $endtime, PDO::PARAM_STR);
            $stmt_schedule->bindParam(':sno', $selected_screen, PDO::PARAM_INT);
    
            // SQLを実行
            $stmt_schedule->execute();
    
            // 実行結果を取得
            $schedule = $stmt_schedule->fetch(PDO::FETCH_ASSOC);
            if (!$schedule) {
                return [];  // スケジュールが見つからなかった場合は空の配列を返す
            }
            $schedule_id = $schedule['schedule_id'];
    
            // 座席情報を取得するSQL文
            $sql_seats = "SELECT s.seatno, s.premium, sy.yoyaku
                          FROM seat s
                          LEFT JOIN seat_yoyaku sy ON s.seat_id = sy.seat_id AND sy.schedule_id = :schedule_id
                          WHERE s.sno = :sno";
    
            $stmt_seats = $dbh->prepare($sql_seats);
    
            // パラメータをSQLクエリにバインド
            $stmt_seats->bindParam(':schedule_id', $schedule_id, PDO::PARAM_INT);
            $stmt_seats->bindParam(':sno', $selected_screen, PDO::PARAM_INT);
    
            // SQLを実行
            $stmt_seats->execute();
    
            // 結果を格納する配列
            $seats = [];
            while ($row = $stmt_seats->fetch(PDO::FETCH_ASSOC)) {
                // 各座席の予約状況をSeat_yoyakuオブジェクトに格納
                $seat_yoyaku = new Seat_yoyaku();
                $seat_yoyaku->seatid = $row['seatno'];  // 座席番号
                $seat_yoyaku->schedule_id = $schedule_id;  // スケジュールID
                $seat_yoyaku->yoyaku = (bool)$row['yoyaku'];  // 予約状況（0=未予約, 1=予約済み）
    
                // 取得した座席情報を配列に追加
                $seats[] = $seat_yoyaku;
            }
    
            return $seats;  // すべての座席情報を返す
        } catch (Exception $e) {
            // エラーログをファイルに記録
            error_log('Error in get_seat_yoyaku: ' . $e->getMessage(), 3, '/path/to/error.log');
            return [];  // エラーが発生した場合は空の配列を返す
        }
    }

    // seat_yoyakuのyoyakuをtrueにするメソッド（座席を予約済みに更新）
    public function get_yoyaku($seat_id, $schedule_id) {
        $dbh = DAO::get_db_connect();  // データベース接続を取得
        
        // 座席の予約状況を更新するSQL文（予約を確定させる）
        $sql = "UPDATE seat_yoyaku
                SET seat_yoyaku.yoyaku = 1
                FROM seat_yoyaku
                WHERE seat_id = :seat_id 
                AND schedule_id = :schedule_id";
        
        $stmt = $dbh->prepare($sql);
        
        // パラメータをSQLクエリにバインド
        $stmt->bindValue(':seat_id', $seat_id, PDO::PARAM_STR);
        $stmt->bindValue(':schedule_id', $schedule_id, PDO::PARAM_INT);
        
        // SQLを実行
        $stmt->execute();
        
        // 影響を受けた行数を確認し、更新が成功したかを返す
        if ($stmt->rowCount() > 0) {
            return true;  // 更新が成功した場合はtrueを返す
        } else {
            return false;  // 更新が失敗した場合はfalseを返す
        }
    }
}    
?>
