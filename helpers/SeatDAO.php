<?php
require_once 'DAO.php';  // データベース接続のDAOクラスを読み込む

// 座席情報を保持するクラス
class Seat
{
    public int $mcode;          // 映画コード
    public int $ccode;          // 会場コード
    public int $sno;            // スクリーンナンバー
    public string $seatno;      // 座席番号
    public string $seat_no;     // 座席番号（冗長な名前が使われている）
    public string $playdate;    // 上映日
    public string $starttime;   // 開始時間
    public string $endtime;     // 終了時間
    public bool $premium;       // プレミアム座席かどうか
    public $seat_id;            // 座席ID
    public $schedule_id;        // スケジュールID
}

class SeatDAO 
{
    // スクリーンの空席を確認するメソッド
    public function get_seat_available(string $mcode)
    {
        $dbh = DAO::get_db_connect();  // データベース接続を取得

        $sql = "SELECT * FROM movie 
                WHERE mcode = :mcode";  // 映画コードに基づいて映画情報を取得

        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(':mcode', $mcode, PDO::PARAM_INT);
        $stmt->execute();

        // １件分のデータをMovieクラスのオブジェクトとして取得する
        $movie = $stmt->fetchObject('Movie');
        return $movie;  // 取得した映画情報を返す
    }
    
    // 映画コード、スクリーンナンバー、上映日、開始時間、終了時間を引数にして、座席情報を取得するメソッド
    public function get_seat_deta(int $mcode, int $sno, string $playdate, string $starttime, string $endtime)
    {
        $dbh = DAO::get_db_connect();  // データベース接続を取得

        // クエリ：上映日、開始時刻、終了時刻を基に座席情報を取得
        $sql = "SELECT 
                    Sea.sno, 
                    Sea.seatno, 
                    Sea.premium, 
                    Sch.playdate, 
                    Sch.starttime, 
                    Sch.endtime,
                    Sc.ccode,
                    Sea.seat_id,
                    Sch.schedule_id
                FROM 
                    seat AS Sea
                INNER JOIN 
                    schedule AS Sch
                    ON Sea.sno = Sch.sno
                INNER JOIN
                    screen AS Sc
                    ON Sea.sno = Sc.sno
                WHERE 
                    Sch.mcode = :mcode
                    AND Sea.sno = :sno
                    AND Sch.playdate = :playdate
                    AND Sch.starttime >= :starttime
                    AND Sch.endtime <= :endtime
                ORDER BY 
                    Sea.seatno";  // 座席番号でソート

        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(':mcode', $mcode, PDO::PARAM_INT);
        $stmt->bindValue(':sno', $sno, PDO::PARAM_INT);
        $stmt->bindValue(':playdate', $playdate, PDO::PARAM_STR);
        $stmt->bindValue(':starttime', $starttime, PDO::PARAM_STR);
        $stmt->bindValue(':endtime', $endtime, PDO::PARAM_STR);
        $stmt->execute();

        // 結果をSeatクラスのインスタンスとして取得
        $seats = $stmt->fetchAll(PDO::FETCH_CLASS, 'Seat');

        // 結果が空でないかチェック
        if ($seats) {
            foreach ($seats as $seat) {
                // nullの処理を行う
                $seat->starttime = $seat->starttime ?? ''; // nullなら空文字
                $seat->endtime = $seat->endtime ?? '';     // nullなら空文字
            }
        }

        return $seats;  // 座席情報を返す
    }

    // 映画コード、スクリーンナンバーを引数にして、座席の空き状況を取得するメソッド
    public function get_capacity(int $mcode, int $sno)
    {
        $dbh = DAO::get_db_connect();  // データベース接続を取得
        $sql = "SELECT COUNT(*) AS seat_count 
        FROM seat_yoyaku AS Y
        INNER JOIN schedule AS Sch
        ON Y.schedule_id = Sch.schedule_id
        WHERE Sch.mcode = :mcode  -- scheduleテーブルからmcodeを参照
        AND sno = :sno 
        AND Y.yoyaku = 0";  // 予約されていない座席をカウント

        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(':mcode', $mcode, PDO::PARAM_INT);
        $stmt->bindValue(':sno', $sno, PDO::PARAM_INT);
        $stmt->execute();
    
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['seat_count'] ?? 0;  // 空席数を返す、デフォルトは0
    }
    
    // 映画コード、会場コード、開始時間を引数として、予約数を取得するメソッド
    public function get_yoyaku(int $mcode, int $ccode, string $starttime, string $endtime)
    {
        $dbh = DAO::get_db_connect();  // データベース接続を取得
        $sql = "SELECT COUNT(Y.yoyaku) AS count
                FROM seat AS Sea
                INNER JOIN schedule AS Sch ON Sea.sno = Sch.sno
                INNER JOIN screen AS Sc ON Sea.sno = Sc.sno
                INNER JOIN seat_yoyaku AS Y ON Sea.seat_id = Y.seat_id
                WHERE Sch.mcode = :mcode  -- scheduleテーブルのmcode
                AND Sc.ccode = :ccode     -- screenテーブルのccode
                AND Y.yoyaku = 1        -- 予約済みの座席
                AND Sch.starttime >= :starttime
                AND Sch.endtime < :endtime";  // スケジュールの時間範囲

        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(':mcode', $mcode, PDO::PARAM_INT);
        $stmt->bindValue(':ccode', $ccode, PDO::PARAM_INT);
        $stmt->bindValue(':starttime', $starttime, PDO::PARAM_STR);
        $stmt->bindValue(':endtime', $endtime, PDO::PARAM_STR);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;  // 予約数がない場合は0
    }

    // 座席数から予約数を引いたチケット予約可能数を取得するメソッド
    public function get_available_tickets($mcode, $ccode, $starttime, $endtime, $sno)
    {   
        // キャパシティ（空席数）を取得
        $capacity = $this->get_capacity($mcode, $sno);

        // 指定された時間範囲の予約数を取得
        $yoyaku = $this->get_yoyaku($mcode, $ccode, $starttime, $endtime);

        // 予約可能数を計算
        if ($capacity !== null) {
            $available_tickets = $capacity - $yoyaku;
        } else {
            // キャパシティが取得できない場合
            $available_tickets = 0;
        }

        return max($available_tickets, 0);  // 0未満にならないように
    }
    
    // 開始時間が遅い上映かどうかを判断するメソッド
    public function is_late_show(string $starttime, string $late_show_time = '20:00'): bool
    {
        return strtotime($starttime) >= strtotime($late_show_time);  // 開始時間が20:00以降か確認
    }
    
    // 座席番号とスクリーン番号を基に座席IDを取得するメソッド
    public function get_seat_id($seat_no, $sno) 
    {
        $dbh = DAO::get_db_connect();  // データベース接続を取得
    
        $sql = "SELECT seat_id
                FROM seat
                WHERE seatno = :seat_no
                AND sno = :sno";  // 座席番号とスクリーン番号を基に座席IDを取得
    
        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(':seat_no', $seat_no, PDO::PARAM_STR);
        $stmt->bindValue(':sno', $sno, PDO::PARAM_INT);
        $stmt->execute(); 
        
        // 1件のみ取得する場合
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
        return $result ? $result['seat_id'] : null;  // seat_idが取得できない場合はnullを返す
    }
    
    // プレミアムシートの数を取得するメソッド
    public function get_seat_premium($seatnos, $sno)
    {
        // 引数が文字列の場合、カンマで分割して配列に変換
        if (is_string($seatnos)) {
            $seatnos = explode(',', $seatnos);
        }

        $dbh = DAO::get_db_connect();  // データベース接続を取得

        // プレミアムなシートの件数をカウントするSQLクエリ
        // IN句の?を動的に生成
        $placeholders = implode(',', array_fill(0, count($seatnos), '?'));
        $sql = "SELECT COUNT(*) as count FROM seat WHERE premium = 1 AND seatno IN ($placeholders) AND sno = ?";

        $stmt = $dbh->prepare($sql);

        // プレースホルダに値をバインド
        foreach ($seatnos as $index => $seatno) {
            $stmt->bindValue($index + 1, $seatno, PDO::PARAM_STR);  // seatnoのバインド
        }

        // snoのバインド
        $stmt->bindValue(count($seatnos) + 1, $sno, PDO::PARAM_INT);  // snoのバインド

        $stmt->execute();

        // 結果を取得
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // カウント結果を返す
        return $result['count'];
    }

    // プレミアムシートかどうかを確認するメソッド
    public function get_premium($seatno, $sno)
    {
        $dbh = DAO::get_db_connect();  // データベース接続を取得
        
        $sql = "SELECT premium FROM seat WHERE premium = 1 AND seatno = :seatno AND sno = :sno";  // プレミアムシートの確認
        $stmt = $dbh->prepare($sql);

        $stmt->bindValue(':seatno', $seatno, PDO::PARAM_STR);
        $stmt->bindValue(':sno', $sno, PDO::PARAM_INT);
        $stmt->execute();

        // クエリの結果を取得
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // 結果が存在する場合はtrueを返す
        if ($result !== false) {
            return true;
        } else {
            return false;
        }
    }
    
    // 座席IDを取得するメソッド
    public function get_seat($seat_id)
    {
        $dbh = DAO::get_db_connect();  // データベース接続を取得
   
        $sql = "SELECT * FROM seat 
                   WHERE seat_id= :seat_id";  // 座席IDを基に座席情報を取得
   
        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(':seat_id', $seat_id, PDO::PARAM_INT);
        $stmt->execute();
   
        // １件分のデータをSeatクラスのオブジェクトとして取得する
        $seat = $stmt->fetchObject('seat');
        return $seat;  // 取得した座席情報を返す
    }

    // 座席番号とスクリーン番号を基に座席IDを取得するメソッド
    public function get_seatid($sno, $seat_no) 
    {
        $dbh = DAO::get_db_connect();  // データベース接続を取得
            
        $sql = "SELECT seat_id
                FROM seat
                WHERE seatno = :seat_no 
                AND sno = :sno";  // 座席番号とスクリーン番号を基に座席IDを取得
            
        // ステートメントの準備
        $stmt = $dbh->prepare($sql);
        
        // パラメータのバインド
        $stmt->bindValue(':sno', $sno, PDO::PARAM_INT);
        
        // $seat_noが配列でない場合、または単一の値として渡された場合に対応
        if (is_array($seat_no)) {
            $seat_no = implode(',', $seat_no); // 配列をカンマ区切りの文字列に変換
        }
    
        // $seat_noが空でないことを確認
        if (empty($seat_no)) {
            throw new Exception("座席番号が指定されていません。");
        }
    
        $stmt->bindValue(':seat_no', $seat_no, PDO::PARAM_STR); // $seat_no はカンマ区切りの文字列に変換されている
    
        // クエリ実行
        $stmt->execute();
        
        // 結果を取得
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // seat_idがあればその値を返す
        if ($result) {
            return $result['seat_id'];
        } else {
            return null; // 結果がなければnullを返す
        }
    }
}
?>
