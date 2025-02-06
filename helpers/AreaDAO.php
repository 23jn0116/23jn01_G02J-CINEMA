<?php
    require_once 'DAO.php'; // データベース接続を管理するDAOをインクルード

    // エリア情報を保持するクラス
    class area
    {
        public int $areacode; // エリアコード
        public string $areaname; // エリア名
    }

    // エリア情報を操作するDAOクラス
    class AreaDAO
    {
        // 全てのエリアを取得するメソッド
        public function get_area()
        {
            // DB接続を取得
            $dbh = DAO::get_db_connect();

            // SQLクエリ（全エリアの取得）
            $sql  = "SELECT * FROM area";
            // SQL文を準備
            $stmt = $dbh->prepare($sql);

            // SQLを実行
            $stmt->execute();

            // データを格納する配列
            $data = [];
            // fetchObjectを使用して、エリアクラスのオブジェクトとして結果を取得
            while($row = $stmt->fetchObject('area')){
                // 取得したデータを配列に追加
                $data[] = $row;
            }

            // エリア情報の配列を返す
            return $data;
        }

        // キーワードでエリアを検索して取得するメソッド
        public function get_area_by_keyword(string $keyword)
        {
            // DB接続を取得
            $dbh = DAO::get_Db_connect();

            // SQLクエリ（エリア名で検索）
            $sql = "SELECT *
                    FROM area
                    WHERE areaname LIKE :areaname";

            // SQL文を準備
            $stmt = $dbh->prepare($sql);

            // プレースホルダーに値をバインド（キーワードを含むエリア名を検索）
            $stmt->bindValue(':areaname', '%'.$keyword.'%', PDO::PARAM_STR);

            // SQLを実行
            $stmt->execute();

            // 検索結果を格納する配列
            $data = [];
            // fetchObjectを使用して、エリアクラスのオブジェクトとして結果を取得
            while($row = $stmt->fetchObject('Area')) {
                // 取得したデータを配列に追加
                $data[] = $row;
            }

            // 検索結果のエリア情報の配列を返す
            return $data;
        }

        // 特定のスクリーン番号(sno)に関連する会場コード(ccode)を取得するメソッド
        public function get_ccode_by_sno(int $sno) 
        {
            // DB接続を取得
            $dbh = DAO::get_db_connect();

            // SQLクエリ（スクリーン番号から会場コードを取得）
            $sql = "SELECT ccode FROM screen WHERE sno = :sno";

            // SQL文を準備
            $stmt = $dbh->prepare($sql);
            // プレースホルダーにスクリーン番号をバインド
            $stmt->bindValue(':sno', $sno, PDO::PARAM_INT);
            // SQLを実行
            $stmt->execute();

            // 結果を取得（配列として返す）
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            // 会場コードが存在すれば返す、なければnullを返す
            return !empty($result) ? $result['ccode'] : null;
        }
    }
?>
