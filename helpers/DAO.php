<?php
    // config.phpをインクルードして、データベース接続に必要な設定を読み込む
    require_once 'config.php';

    // データベース接続を管理するDAOクラス
    class DAO {
        // データベース接続を保持する静的プロパティ（初回接続時のみ接続を作成）
        private static $dbh;

        // データベース接続を取得する静的メソッド
        public static function get_db_connect()
        {
            try {
                // 接続がまだ作成されていない場合
                if (self::$dbh === null) { 
                    // PDOを使ってデータベースに接続（DSN, DB_USER, DB_PASSWORDはconfig.phpに定義されている）
                    self::$dbh = new PDO(DSN, DB_USER, DB_PASSWORD);
                }
            }
            catch (PDOException $e) { 
                // データベース接続エラーが発生した場合のエラーハンドリング
                echo $e->getMessage(); // エラーメッセージを表示
                die(); // スクリプトを終了
            }
            // データベース接続オブジェクト（PDO）を返す
            return self::$dbh;
        }
    }
?>
