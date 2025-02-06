<?php
require_once 'DAO.php';  // データベース接続のDAOクラスを読み込む

// スクリーンに関連する情報を保持するScreenクラス
class Screen 
{
    public int $ccode;              // 地域コード
    public int $sno;                // スクリーンナンバー
    public int $mcode;              // 映画コード
    public int $capacity;           // 収容人数
}

class ScreenDAO
{
    // 上映日を引数にして、上映中のスクリーン番号を取得するメソッド
    public function get_sno(string $playdate) 
    {
        // 引数で渡された上映日を適切な日付形式（Y-m-d）に変換
        $playdate = date('Y-m-d', strtotime($playdate));

        $dbh = DAO::get_db_connect();  // データベース接続を取得

        // 映画の上映日を基にスクリーン番号を取得するSQL文
        $sql = "SELECT sno FROM schedule 
                WHERE playdate = :playdate";
        $stmt = $dbh->prepare($sql);

        // playdateをSQLクエリのパラメータとしてバインド
        $stmt->bindValue(':playdate', $playdate, PDO::PARAM_STR);

        // SQLを実行
        $stmt->execute();

        // 結果をすべて取得し、スクリーン番号を配列で返す
        $screen_numbers = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // 結果があればスクリーン番号の配列を返し、なければnullを返す
        return !empty($screen_numbers) ? $screen_numbers : null;
    }

    // スクリーン番号、映画コード、上映日を引数にして、上映の開始時間と終了時間を取得するメソッド
    public function get_screentime(int $sno, int $mcode, string $playdate) 
    {
        $dbh = DAO::get_db_connect();  // データベース接続を取得

        // スクリーン番号、映画コード、上映日に基づいて、開始時間と終了時間を取得するSQL文
        $sql = "SELECT DISTINCT starttime, endtime FROM schedule
                WHERE sno = :sno AND mcode = :mcode AND playdate = :playdate";
        $stmt = $dbh->prepare($sql);

        // パラメータをSQLクエリにバインド
        $stmt->bindValue(':sno', $sno, PDO::PARAM_INT);
        $stmt->bindValue(':mcode', $mcode, PDO::PARAM_INT);
        $stmt->bindValue(':playdate', $playdate, PDO::PARAM_STR);

        // SQLを実行
        $stmt->execute();

        // 結果を全て取得し、開始時間と終了時間の配列を返す
        $screen_times = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 結果があれば配列を返し、なければnullを返す
        return $screen_times;
    }

    // 指定された時間帯に空いている座席数を取得するメソッド
    public function get_available_tickets($starttime, $endtime, $sno) 
    {
        $dbh = DAO::get_db_connect();  // データベース接続を取得

        // 座席の予約状況に基づいて、指定された時間帯に利用可能な座席数を取得するSQL文
        $sql = "SELECT COUNT(*) AS available_tickets
                FROM seat AS se
                INNER JOIN schedule AS sc ON se.sno = sc.sno
                WHERE se.sno = :sno
                  AND sc.starttime <= :starttime AND sc.endtime >= :endtime
                  AND se.yoyaku = 0";  // yoyakuが0のもの（予約されていない座席）

        $stmt = $dbh->prepare($sql);

        // パラメータをバインド
        $stmt->bindValue(':starttime', $starttime, PDO::PARAM_STR);
        $stmt->bindValue(':endtime', $endtime, PDO::PARAM_STR);
        $stmt->bindValue(':sno', $sno, PDO::PARAM_INT);

        // SQLを実行
        $stmt->execute();

        // 結果を取得
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // 利用可能な座席数を取得（結果がなければ0を返す）
        $available_tickets = isset($result['available_tickets']) ? $result['available_tickets'] : 0;

        return $available_tickets;  // 利用可能な座席数を返す
    }
}
?>
