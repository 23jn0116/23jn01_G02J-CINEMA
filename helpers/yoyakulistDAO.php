<?php
require_once 'DAO.php'; // データベース接続のためのDAOクラスを読み込む

// 予約リストを保持するためのクラス
class yoyakulist {
    public int $kno;          // 顧客番号
    public int $mcode;        // 映画コード
    public int $yoyaku_id;    // 予約ID
    public string $QRcode;    // QRコード（文字列型）
    public string $playdate;  // 上映日（文字列型）
    public string $seat;      // 座席番号
    public string $time;      // 上映時間
    public string $areaname;  // エリア名
}

class yoyakulistDAO
{
    private PDO $pdo;  // PDOインスタンス

    // コンストラクタ：データベース接続を初期化
    public function __construct()
    {
        $this->pdo = DAO::get_Db_connect(); // DAOクラスのデータベース接続を取得
    }

    // QRコードを取得するメソッド
    public function getQRCode(int $yoyakuid): ?string
    {
        try {
            $sql = "SELECT QRcode FROM yoyakulist WHERE yoyakuid = :yoyakuid";  // QRコードを取得するSQL
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':yoyakuid', $yoyakuid, PDO::PARAM_INT); // yoyakuidをバインド
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC); // 1行を連想配列で取得
            return $row['QRcode'] ?? null;  // QRコードがあれば返す、なければnull
        } catch (PDOException $e) {
            // エラーログに記録
            error_log('Error fetching QR code: ' . $e->getMessage(), 3, '/path/to/error.log');
            return null;  // エラー発生時はnullを返す
        }
    }

    // 予約情報を取得するメソッド
    public function get_yoyaku(int $kno): ?array
    {
        $sql = "SELECT * FROM yoyakulist WHERE kno = :kno";  // 顧客番号で予約情報を検索
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':kno', $kno, PDO::PARAM_INT);  // knoをバインド
        $stmt->execute();

        // 結果を格納する配列
        $data = [];

        // データベースの結果をループして配列に格納
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $data[] = $row;  // 予約情報を配列に追加
        }

        // 結果があればデータを返す、なければnullを返す
        return count($data) > 0 ? $data : null;
    }

    // 映画情報を取得するメソッド
    public function get_movie(int $mcode): ?array
    {
        $sql = "SELECT * FROM movie WHERE mcode = :mcode";  // 映画コードで映画情報を検索
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':mcode', $mcode, PDO::PARAM_INT);  // mcodeをバインド
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);  // 結果を連想配列で返す
    }

    // 予約情報を挿入するメソッド
    public function insert_yoyaku(int $kno, int $mcode, string $playdate, string $seat, string $time, string $filename, string $areaname)
    {
        $dbh = DAO::get_db_connect();  // データベース接続を取得
        $sql = "INSERT INTO yoyakulist (kno, mcode, playdate, seat, time, QRcode, areaname) 
                VALUES (:kno, :mcode, :playdate, :seat, :time, :QRcode, :areaname)";  // 予約情報を挿入するSQL

        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(':kno', $kno, PDO::PARAM_INT);  // 顧客番号
        $stmt->bindValue(':mcode', $mcode, PDO::PARAM_INT);  // 映画コード
        $stmt->bindValue(':playdate', $playdate, PDO::PARAM_STR);  // 上映日
        $stmt->bindValue(':seat', $seat, PDO::PARAM_STR);  // 座席
        $stmt->bindValue(':time', $time, PDO::PARAM_STR);  // 上映時間
        $stmt->bindValue(':QRcode', $filename, PDO::PARAM_STR);  // QRコードファイル名
        $stmt->bindValue(':areaname', $areaname, PDO::PARAM_STR);  // エリア名

        $stmt->execute();  // クエリ実行
    }

    // 予約リストを取得するメソッド
    public function get_yoyakulist(int $yoyaku_id)
    {
        $dbh = DAO::get_db_connect();  // データベース接続を取得

        $sql = "SELECT * FROM yoyakulist WHERE yoyaku_id = :yoyaku_id";  // 予約IDで検索
        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(':yoyaku_id', $yoyaku_id, PDO::PARAM_INT);  // yoyaku_idをバインド

        $stmt->execute();
        
        // 結果を格納する配列
        $data = [];
        while ($row = $stmt->fetchObject('yoyakulist')) {  // yoyakulistクラスのオブジェクトとして取得
            $data[] = $row;  // 取得したデータを配列に追加
        }

        return $data;  // 予約リストを返す
    }

    // 予約情報を削除するメソッド
    public function yoyaku_delete(int $yoyaku_id)
    {
        $dbh = DAO::get_db_connect();  // データベース接続を取得
        $sql = "DELETE FROM yoyakulist WHERE yoyaku_id = :yoyaku_id";  // 予約IDを基に削除するSQL

        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(':yoyaku_id', $yoyaku_id, PDO::PARAM_INT);  // yoyaku_idをバインド

        $stmt->execute();  // クエリ実行
    }
}
?>
