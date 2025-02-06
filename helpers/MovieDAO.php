<?php
require_once 'DAO.php'; // データベース接続用のDAOクラスをインクルード

// 映画に関する情報を保持するMovieクラス
class Movie
{
    public int    $mcode;       // 映画コード（映画の一意な識別子）
    public string $mname;       // 映画名
    public string $detail;      // 映画の詳細説明
    public string $koukaidate;  // 映画の公開日
    public string $enddate;     // 映画の公開終了日
    public string $areacode;    // 地域コード（映画の公開地域）
    public string $genrecode;   // 映画のジャンルコード
    public string $r;           // 映画のレイティング（例えばPG-13など）
    public int    $recommend;   // おすすめ映画かどうか（1：おすすめ、0：非おすすめ）
    public string $photo;       // 映画の写真（写真のファイル名）
}

// Movieクラスに関連するデータベース操作を行うMovieDAOクラス
class MovieDAO
{
    // 全ての映画情報をデータベースから取得するメソッド
    public function get_movie() 
    {
        $dbh = DAO::get_db_connect(); // データベース接続を取得

        // 映画情報を全て取得するSQL文
        $sql = "SELECT * FROM movie"; 
        $stmt = $dbh->prepare($sql); // SQL文を準備
        
        $stmt->execute(); // SQLを実行

        $data = [];  // 取得した映画データを格納する配列
        while($row = $stmt->fetchObject('Movie')) {
            $data[] = $row; // Movieオブジェクトとして映画情報を取得
        }

        return $data; // 取得した映画情報の配列を返す
    }

    // 指定した映画コード（mcode）の映画情報を取得するメソッド
    public function get_mcode_movie(int $mcode)
    {
        $dbh = DAO::get_db_connect();  // データベース接続を取得

        // 指定した映画コードに基づき、映画情報を取得するSQL文
        $sql = "SELECT * FROM movie WHERE mcode = :mcode"; 

        $stmt = $dbh->prepare($sql);  // SQL文を準備

        // SQL文に映画コードをバインド
        $stmt->bindValue(':mcode', $mcode, PDO::PARAM_INT);

        $stmt->execute();  // SQL実行

        // 1件分の映画情報をMovieオブジェクトとして取得
        $movie = $stmt->fetchObject('Movie'); 
        return $movie; // 取得した映画情報を返す
    }

    // 映画コードに基づいて直近の公開日を取得するメソッド
    // 公開日を基に、今後3日以内の公開予定日を取得
    public function get_koukaidate(int $mcode)
    {
        $dbh = DAO::get_db_connect();  // データベース接続を取得

        // 指定した映画コードで、公開日が現在日付より先のものを取得
        $sql = "SELECT TOP 3 playdate FROM schedule
                WHERE mcode = :mcode
                AND playdate > GETDATE()
                ORDER BY playdate ASC";

        $stmt = $dbh->prepare($sql);  // SQL文を準備

        // 映画コードをSQLにバインド
        $stmt->bindParam(':mcode', $mcode, PDO::PARAM_STR);

        $stmt->execute();  // SQL実行

        // 取得した公開日情報を配列で返す
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $results;  // 直近の公開日情報を返す
    }

    // おすすめ映画を取得するメソッド
    public function get_recommend_movie()
    {
        $dbh = DAO::get_Db_connect(); // データベース接続を取得

        // おすすめ映画（recommend = 1）の情報を取得するSQL文
        $sql = "SELECT * FROM movie WHERE recommend = 1"; 

        $stmt = $dbh->prepare($sql);  // SQL文を準備

        $stmt->execute();  // SQL実行

        $data = [];  // おすすめ映画の情報を格納する配列
        while($row = $stmt->fetchObject('Movie')) {
            $data[] = $row;  // Movieオブジェクトとして映画情報を取得
        }

        return $data;  // おすすめ映画情報の配列を返す
    }

    // 映画情報をデータベースに新規挿入するメソッド
    public function insert(Movie $movie) 
    {
        $dbh = DAO::get_db_connect();  // データベース接続を取得
        
        // 映画情報を挿入するためのSQL文
        $sql = "INSERT INTO Movie (mname, detail, koukaidate, enddate, genrecode, r, recommend, photo)
                VALUES (:mname, :detail, :koukaidate, :enddate, :genrecode, :r, :recommend, :photo)";
        
        $stmt = $dbh->prepare($sql);  // SQL文を準備
        
        // プレースホルダに映画オブジェクトの各属性をバインド
        $stmt->bindValue(':mname', $movie->mname, PDO::PARAM_STR);
        $stmt->bindValue(':detail', $movie->detail, PDO::PARAM_STR);
        $stmt->bindValue(':koukaidate', $movie->koukaidate, PDO::PARAM_STR);
        $stmt->bindValue(':enddate', $movie->enddate, PDO::PARAM_STR);    
        $stmt->bindValue(':genrecode', $movie->genrecode, PDO::PARAM_INT);
        $stmt->bindValue(':r', $movie->r, PDO::PARAM_STR);     
        $stmt->bindValue(':recommend', $movie->recommend, PDO::PARAM_INT);
        $stmt->bindValue(':photo', $movie->photo, PDO::PARAM_STR);  // 写真ファイル名
        
        $stmt->execute();  // SQL実行
    }

    // キーワードで映画名を検索し、該当する映画情報を取得するメソッド
    public function get_movie_by_keyword(string $keyword)
    { 
        $dbh = DAO::get_Db_connect();  // データベース接続を取得

        // 映画名にキーワードが含まれている映画を取得するSQL文
        $sql = "SELECT * FROM movie WHERE mname LIKE :mname";

        $stmt = $dbh->prepare($sql);  // SQL文を準備

        // キーワードを含む映画名をバインド
        $stmt->bindValue(':mname', '%'.$keyword.'%', PDO::PARAM_STR);

        $stmt->execute();  // SQL実行

        $data = [];  // 検索結果を格納する配列
        while($row = $stmt->fetchObject('Movie')) {
            $data[] = $row;  // Movieオブジェクトとして映画情報を取得
        }

        return $data;  // 検索結果を返す
    }

    // 映画情報を更新するメソッド
    public function update_movie($mcode, $mname, $detail, $koukaidate, $enddate, $genrecode, $r, $recommend, $photoname)
    {
        $dbh = DAO::get_db_connect();  // データベース接続を取得
        
        // 映画情報を更新するためのSQL文
        $sql = "UPDATE movie SET mname = :mname, detail = :detail, koukaidate = :koukaidate, 
                enddate = :enddate, genrecode = :genrecode, r = :r, recommend = :recommend, photo = :photo 
                WHERE mcode = :mcode";
        
        $stmt = $dbh->prepare($sql);  // SQL文を準備
        
        // 更新する映画情報をバインド
        $stmt->bindValue(':mname', $mname, PDO::PARAM_STR);
        $stmt->bindValue(':detail', $detail, PDO::PARAM_STR);
        $stmt->bindValue(':koukaidate', $koukaidate, PDO::PARAM_STR);
        $stmt->bindValue(':enddate', $enddate, PDO::PARAM_STR);    
        $stmt->bindValue(':genrecode', $genrecode, PDO::PARAM_INT);
        $stmt->bindValue(':r', $r, PDO::PARAM_STR);     
        $stmt->bindValue(':recommend', $recommend, PDO::PARAM_INT);
        $stmt->bindValue(':photo', $photoname, PDO::PARAM_STR);
        $stmt->bindValue(':mcode', $mcode, PDO::PARAM_INT);
        
        $stmt->execute();  // SQL実行
    }

    // 指定した映画コードに基づいて映画情報を削除するメソッド
    public function delete_movie($mcode)
    {
        $dbh = DAO::get_db_connect();  // データベース接続を取得
        
        // 映画情報を削除するSQL文
        $sql = "DELETE FROM movie WHERE mcode = :mcode"; 
        
        $stmt = $dbh->prepare($sql);  // SQL文を準備
        
        // 映画コードをバインド
        $stmt->bindValue(':mcode', $mcode, PDO::PARAM_INT);  
        
        $stmt->execute();  // SQL実行
    }

    // 年齢と映画のレイティングを比較し、視聴可否を判断するメソッド
    public function review_rating($age, $mcode)
    {
        $dbh = DAO::get_db_connect();  // データベース接続を取得
        
        // 映画のレイティングを取得するSQL文
        $sql = "SELECT r FROM movie WHERE mcode = :mcode";
        $stmt = $dbh->prepare($sql);
        
        // 映画コードをバインド
        $stmt->bindValue(':mcode', $mcode, PDO::PARAM_INT);
        $stmt->execute();
    
        // レイティングを取得
        $movie = $stmt->fetch(PDO::FETCH_ASSOC);
    
        $rating = (int)$movie['r'];  // 映画のレイティングを整数型にキャスト
    
        // 年齢と映画のレイティングを比較
        if ($age < $rating) {
            return false;  // レイティングに満たないので視聴不可
        }
    
        return true;  // レイティングを満たしているので視聴可能
    }
}
?>
