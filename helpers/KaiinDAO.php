<?php
    require_once 'DAO.php'; // DAOクラスをインクルードしてデータベース接続を使用可能にする

    // 会員情報を保持するクラス
    class Kaiin 
    { 
        public int    $kno;         // 会員ID
        public string $kname;       // 漢字名
        public string $kananame;    // カナ名
        public int    $age;         // 年齢
        public string $seibetu;     // 性別
        public string $email;       // メールアドレス
        public string $tel;         // 電話番号
        public string $oldpass;     // 旧パスワード
        public string $newpass;     // 新パスワード
        public string $yoyakuno;    // 予約番号
        public int    $kanrikengen; // 管理者権限
    }

    // 会員情報に対するデータ操作を行うDAOクラス
    class KaiinDAO {
        
        // すべての会員情報を取得するメソッド
        public function get_member() 
        {
            // データベース接続を取得
            $dbh = DAO::get_db_connect();

            // 会員情報を取得するSQLクエリ
            $sql = "SELECT kno,kananame,email,oldpass,kanrikengen,tel FROM kaiin"; 
            $stmt = $dbh->prepare($sql);
            
            // SQLを実行
            $stmt->execute();

            // 結果を格納する配列
            $data = [];
            
            // フェッチしてデータを格納
            while($row = $stmt->fetchObject('Kaiin')) {
                $data[] = $row;
            }

            // 会員情報の配列を返す
            return $data;
        } 

        // メールアドレスと旧パスワードで会員情報を取得するメソッド
        public function get_kaiin(string $email, string $oldpass) 
        {
            // データベース接続を取得
            $dbh = DAO::get_db_connect();

            // メールアドレスに一致する会員情報を取得するSQLクエリ
            $sql = "SELECT kno,kananame,email,oldpass,kanrikengen,age FROM kaiin WHERE email = :email"; 
            $stmt = $dbh->prepare($sql);

            // プレースホルダにメールアドレスをバインド
            $stmt->bindValue(':email',$email,PDO::PARAM_STR);
            
            // SQLを実行
            $stmt->execute();

            // 結果をオブジェクトとして取得
            $kaiin = $stmt->fetchObject('Kaiin');

            // 会員が見つかり、パスワードが一致すれば会員情報を返す
            if ($kaiin !== false) { 
                if (password_verify($oldpass, $kaiin->oldpass)) { 
                    return $kaiin; 
                } 
            } 
            // それ以外の場合はfalseを返す
            return false; 
        } 

        // 新しい会員情報をデータベースに挿入するメソッド
        public function insert(Kaiin $kaiin) 
        {
            // データベース接続を取得
            $dbh = DAO::get_db_connect();
            
            // パスワードをハッシュ化
            $password = password_hash($kaiin->oldpass, PASSWORD_DEFAULT);
           
            // 新しい会員情報を挿入するSQLクエリ
            $sql = "INSERT INTO Kaiin (kname, kananame, age, seibetu, email, oldpass, tel, kanrikengen) 
                    VALUES (:kname, :kananame, :age, :seibetu, :email, :password, :tel, 0)";
            
            // SQL文を準備
            $stmt = $dbh->prepare($sql);
         
            // プレースホルダに会員情報をバインド
            $stmt->bindValue(':kname', $kaiin->kname, PDO::PARAM_STR);
            $stmt->bindValue(':kananame', $kaiin->kananame, PDO::PARAM_STR);
            $stmt->bindValue(':age', $kaiin->age, PDO::PARAM_INT);
            $stmt->bindValue(':seibetu', $kaiin->seibetu, PDO::PARAM_STR);
            $stmt->bindValue(':email', $kaiin->email, PDO::PARAM_STR);
            $stmt->bindValue(':password', $password, PDO::PARAM_STR);
            $stmt->bindValue(':tel', $kaiin->tel, PDO::PARAM_INT);         
            
            // SQLを実行
            $stmt->execute();
        }
        
        // メールアドレスが既に存在するかをチェックするメソッド
        public function email_exists(string $email) 
        {
            // データベース接続を取得
            $dbh = DAO::get_db_connect();

            // メールアドレスが存在するかを確認するSQLクエリ
            $sql = "SELECT * FROM Kaiin WHERE email = :email"; 
            $stmt = $dbh->prepare($sql);

            // プレースホルダにメールアドレスをバインド
            $stmt->bindValue(':email', $email, PDO::PARAM_STR);
            $stmt->execute();

            // 結果が存在すればtrueを返し、なければfalseを返す
            if ($stmt->fetch() !== false) {
                return true;  
            } else {
                return false; 
            }
        }

        // 会員のパスワードを更新するメソッド
        public function update_password($kno, $newpass)
        {
            // データベース接続を取得
            $dbh = DAO::get_db_connect();
            
            // 新しいパスワードをハッシュ化
            $hashed_password = password_hash($newpass, PASSWORD_DEFAULT);
            
            // SQLクエリを準備（会員IDのパスワードを更新）
            $sql = "UPDATE kaiin SET oldpass = :password WHERE kno = :kno";
            
            // SQL文を準備
            $stmt = $dbh->prepare($sql);
            
            // プレースホルダに会員IDと新しいパスワードをバインド
            $stmt->bindValue(':kno', $kno, PDO::PARAM_INT);
            $stmt->bindValue(':password', $hashed_password, PDO::PARAM_STR);
            
            // SQL文を実行し、成功したらtrueを返す
            if ($stmt->execute()) {
                return true;  // パスワード更新が成功した場合
            } else {
                return false; // パスワード更新が失敗した場合
            }
        }

        // 会員を削除するメソッド
        public function delete(int $kno) 
        {
            // データベース接続を取得
            $dbh = DAO::get_db_connect();
           
            // 会員IDを基に会員を削除するSQLクエリ
            $sql = "DELETE FROM Kaiin WHERE kno = :kno";
            
            // SQL文を準備
            $stmt = $dbh->prepare($sql);
         
            // プレースホルダに会員IDをバインド
            $stmt->bindValue(':kno', $kno, PDO::PARAM_INT);       
            
            // SQLを実行
            $stmt->execute();
        }

        // 会員IDからパスワードを取得するメソッド
        public function get_pass(int $kno) {
            // データベース接続を取得
            $dbh = DAO::get_db_connect();
        
            // 会員IDに基づいてパスワードを取得するSQLクエリ
            $sql = "SELECT oldpass FROM kaiin WHERE kno = :kno"; 
            $stmt = $dbh->prepare($sql);
            
            // プレースホルダに会員IDをバインド
            $stmt->bindValue(':kno', $kno, PDO::PARAM_INT);      
            $stmt->execute();
        
            // 結果をフェッチしてパスワードを返す
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['oldpass'];
        }
        
    }
?>
