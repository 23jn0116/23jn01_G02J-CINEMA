<?php
require_once 'DAO.php'; // DAOクラスをインクルードし、データベース接続を利用できるようにする

// JECカードの情報を保持するクラス
class JECcard {
    public int $kno; // 会員ID
    public int $zandaka; // 残高
}

// JECカード情報を操作するDAOクラス
class JECcardDAO {
    
    // 会員IDを基にJECカード情報を取得するメソッド
    public function getJECcard(int $kno)
    {
        // データベース接続を取得
        $dbh = DAO::get_db_connect();
        
        // SQL文を準備（会員IDに一致するJECカード情報を取得）
        $sql = "SELECT * FROM JECcard WHERE kno = :kno";
        $stmt = $dbh->prepare($sql);
        
        // プレースホルダに会員IDをバインド
        $stmt->bindValue(':kno', $kno, PDO::PARAM_INT);
        
        // SQLを実行
        $stmt->execute();
        
        // 結果があればJECcardオブジェクトを返し、なければnullを返す
        return $stmt->fetchObject('JECcard') ?: null;
    }

    // JECカード情報を新規に追加するメソッド
    public function insert(int $kno) 
    {
        // データベース接続を取得
        $dbh = DAO::get_db_connect();
       
        // SQL文を作成（新しいJECカード情報を挿入）
        $sql = "INSERT INTO JECcard (kno, zandaka) 
                VALUES (:kno, :zandaka)";
        
        // SQL文を準備
        $stmt = $dbh->prepare($sql);
     
        // プレースホルダに会員IDと初期残高（0）をバインド
        $stmt->bindValue(':kno', $kno, PDO::PARAM_INT);
        $stmt->bindValue(':zandaka', 0, PDO::PARAM_INT);  // 初期残高を0に設定
        
        // SQLを実行
        $stmt->execute();
    }

    // JECカードにチャージを行うメソッド
    public function charge_insert(int $kno, int $selectedcharge) 
    {
        // データベース接続を取得
        $dbh = DAO::get_db_connect(); 
        
        // SQL文を作成（指定した金額で残高を更新）
        $sql = "UPDATE JECcard 
                SET zandaka = zandaka + :selectedcharge
                WHERE kno = :kno";
        
        // SQL文を準備
        $stmt = $dbh->prepare($sql);
        
        // プレースホルダに会員IDとチャージ金額をバインド
        $stmt->bindValue(':kno', $kno, PDO::PARAM_INT);  // 会員ID
        $stmt->bindValue(':selectedcharge', $selectedcharge, PDO::PARAM_INT);  // チャージ金額
        
        // SQLを実行して残高更新を反映
        $stmt->execute();
        
        // チャージが成功した場合はtrueを返す
        return true;    
    }
}    
