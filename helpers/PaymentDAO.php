<?php
require_once 'DAO.php';  // データベース接続のDAOクラスを読み込む

// 決済に関連する情報を保持するPaymentクラス
class Payment 
{
    public int $pcode;   // 決済コード（決済を一意に識別するためのコード）
    public int $kingaku;  // 決済金額（決済された金額）

    // コンストラクタ：Paymentクラスのインスタンスを初期化するためにpcodeとkingakuを受け取る
    public function __construct(int $pcode, int $kingaku) {
        $this->pcode = $pcode;  // 決済コードを設定
        $this->kingaku = $kingaku;  // 決済金額を設定
    }
}

// 決済データベース操作を行うDAOクラス
class PaymentDAO 
{
    // 決済情報をデータベースに保存するメソッド
    public function savePayment(Payment $payment): bool 
    {
        $dbh = DAO::get_db_connect();  // データベース接続を取得

        // 決済情報を保存するSQL文
        $sql = "INSERT INTO Payment (pcode, kingaku) VALUES (:pcode, :kingaku)";
        $stmt = $dbh->prepare($sql);  // SQL文を準備

        // パラメータをSQL文にバインド
        $stmt->bindParam(':pcode', $payment->pcode, PDO::PARAM_INT);
        $stmt->bindParam(':kingaku', $payment->kingaku, PDO::PARAM_INT);

        // SQL文を実行し、成功した場合はtrueを返す
        return $stmt->execute();
    }

    // 残高から決済金額を引くメソッド
    public function Payment(int $kno, int $kingaku) 
    {
        $dbh = DAO::get_db_connect();  // データベース接続を取得

        try {
            // 会員番号（kno）を基に、JECcardテーブルから現在の残高を取得するSQL文
            $sql = "SELECT zandaka FROM JECcard WHERE kno = :kno";
            $stmt = $dbh->prepare($sql);  // SQL文を準備
            $stmt->bindParam(':kno', $kno, PDO::PARAM_INT);  // 会員番号をバインド
            $stmt->execute();  // SQL実行

            // 残高を取得
            $result = $stmt->fetch(PDO::FETCH_ASSOC);  // 1行を取得

            // 会員番号が存在する場合
            if ($result) {
                // 現在の残高を取得
                $current_balance = (int)$result['zandaka'];

                // 残高が決済金額以上かを確認
                if ($current_balance >= $kingaku) {
                    // 新しい残高を計算（決済金額を引いた残高）
                    $new_balance = $current_balance - $kingaku;

                    // 新しい残高を更新するSQL文
                    $update_sql = "UPDATE JECcard SET zandaka = :new_balance WHERE kno = :kno";
                    $update_stmt = $dbh->prepare($update_sql);  // 更新SQLの準備
                    $update_stmt->bindParam(':new_balance', $new_balance, PDO::PARAM_INT);  // 新しい残高をバインド
                    $update_stmt->bindParam(':kno', $kno, PDO::PARAM_INT);  // 会員番号をバインド

                    // 残高を更新するSQLを実行
                    if ($update_stmt->execute()) {
                        return "決済が成功しました。";  // 成功メッセージを返す
                    } else {
                        // 残高更新に失敗した場合のエラーメッセージ
                        $errorInfo = $update_stmt->errorInfo();  // エラー情報を取得
                        var_dump($errorInfo);  // エラー情報をダンプして表示（開発中に役立つ）
                        return "決済処理に失敗しました。エラー情報: " . implode(", ", $errorInfo);
                    }
                } else {
                    // 残高不足の場合
                    return "残高が不足しています。";
                }
            } else {
                // 会員番号が存在しない場合
                return "指定された会員番号は存在しません。";
            }

        } catch (PDOException $e) {
            // 例外が発生した場合のエラーメッセージ
            return "エラーが発生しました: " . $e->getMessage();
        }
    }
}
?>
