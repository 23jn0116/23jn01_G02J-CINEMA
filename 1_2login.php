<?php 

    require '../helpers/KaiinDAO.php'; // 会員情報を管理するDAOファイルをインクルード

    $email = ''; // 入力されたメールアドレスを格納する変数を初期化
    $errs  = []; // エラーメッセージを格納する配列を初期化

    session_start(); // セッションを開始

    // すでにログインしている場合、ログイン後のページにリダイレクト
    if(isset($_SESSION['kaiin'])){
        header("Location:1_1index2.php"); // ログイン後のページへリダイレクト
        exit; // スクリプトを終了
    }

    // フォームが送信された場合の処理
    if($_SERVER['REQUEST_METHOD'] === 'POST'){
        $email   = $_POST['email']; // フォームから入力されたメールアドレスを取得
        $oldpass = $_POST['password']; // フォームから入力されたパスワードを取得

        // メールアドレスが空の場合、エラーメッセージを追加
        if($email === ''){
            $errs[] = 'メールアドレスを入力してください'; 
        }
        // メールアドレスの形式が正しくない場合、エラーメッセージを追加
        else if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            $errs[] = 'メールアドレスに誤りがあります。'; 
        }
        // パスワードが空の場合、エラーメッセージを追加
        if($oldpass === ''){
            $errs[] = 'パスワードを入力して下さい。';
        }

        // 入力チェックでエラーがない場合の処理
        if(empty($errs)){
            $kaiinDAO = new KaiinDAO(); // KaiinDAOのインスタンスを作成
            $kaiin = $kaiinDAO->get_kaiin($email, $oldpass); // メールアドレスとパスワードで会員情報を取得

            // 会員情報が正しい場合
            if($kaiin !== false){
                // セッションに会員情報を保存
                $_SESSION['kaiin']   = $kaiin;
                $_SESSION['id']      = $kaiin->kno;
                $_SESSION['age']     = $kaiin->age;
                $_SESSION['purchase_complete'] = false; // 購入完了フラグを初期化
                
                // ログイン完了画面へリダイレクト
                header('Location: 1_2loginEnd.php'); 
                exit; // スクリプトを終了
            } else {
                // 認証失敗時のエラーメッセージを追加
                $errs[] = 'メールアドレスまたはパスワードに誤りがあります。';
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset = "utf-8">
    <link href="../css/LoginStyle.css" rel="stylesheet"> <!-- ログインページ専用のCSSをリンク -->
    <title>ログイン</title> <!-- ページタイトル -->
</head>
<body>
    <?php include "header.php"; ?> <!-- 共通ヘッダーを読み込み -->
    <h1>ログイン</h1> <!-- ページ見出し -->
    <p>以下の項目を入力し、ログインボタンをクリックしてください</p>
    <div class="center-wrapper">
        <div class="center">
            <!-- ログインフォーム -->
            <form action="" method="POST">
                <table>
                    <tr> 
                        <td>会員ID(メールアドレス)</td> 
                        <td> 
                            <!-- メールアドレス入力欄 -->
                            <input type="email" name="email" required autofocus value="<?= htmlspecialchars($email ?? '') ?>"> 
                        </td> 
                    </tr> 
                    <tr> 
                        <td>パスワード</td> 
                        <td> 
                            <!-- パスワード入力欄 -->
                            <input type="password" name="password" required> 
                        </td> 
                    </tr> 
                    <tr class="btn-container"> 
                        <td colspan="2"> 
                            <!-- ログインボタン -->
                            <input type="submit" value="ログイン" class="btn"> 
                        </td> 
                    </tr> 
                    <tr>
                        <td colspan="2">
                            <!-- エラーメッセージを表示 -->
                            <?php foreach($errs as $e) : ?>
                                <span style="color:red"><?= $e ?></span>
                                <br>
                            <?php endforeach; ?>
                        </td>
                    </tr>
                </table> 
            </form>
            <!-- 新規会員登録リンク -->
            <a href="1_3signup.php">新規会員登録はこちら</a>
        </div>
    </div>
</body>
</html>
