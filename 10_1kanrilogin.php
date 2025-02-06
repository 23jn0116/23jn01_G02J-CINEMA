<?php 
    require './helpers/KaiinDAO.php';  // KaiinDAO.phpをインクルードして、データベース操作を実行できるようにする

    $email = '';  // メールアドレスの初期値
    $errs  = [];  // エラーメッセージを格納する配列

    session_start();  // セッションの開始

    // すでにログインしている場合、トップページにリダイレクト
    if(isset($_SESSION['kaiin'])){
        header("Location:1_1index2.php");
        exit;
    }

    // POSTリクエストが送信された場合の処理
    if($_SERVER['REQUEST_METHOD'] === 'POST'){
        $email   = $_POST['email'];  // フォームから送信されたメールアドレスを取得
        $oldpass = $_POST['password'];  // フォームから送信されたパスワードを取得

        // メールアドレスの検証
        if($email === ''){
            $errs[] = 'メールアドレスを入力してください'; 
        }
        else if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            $errs[] = 'メールアドレスに誤りがあります。'; 
        }

        // パスワードの検証
        if($oldpass === ''){
            $errs[] = 'パスワードを入力して下さい。';
        }

        // エラーがなければ、データベースで会員情報を検索
        if(empty($errs)){
            $kaiinDAO = new KaiinDAO();  // KaiinDAOのインスタンスを作成
            $kaiin = $kaiinDAO->get_kaiin($email, $oldpass);  // メールアドレスとパスワードで会員情報を取得

            if(empty($errs)){  // エラーがなければ次の処理
                $kaiinDAO = new KaiinDAO();
                $kaiin = $kaiinDAO->get_kaiin($email, $oldpass);

                // 会員が見つかれば、管理者権限を確認
                if($kaiin !== false){
        
                    // 管理者権限があるか確認
                    if($kaiin->kanrikengen == 1){
                        session_regenerate_id(true);  // セッションIDを再生成（セキュリティ対策）
                
                        $_SESSION['kaiin'] = $kaiin;  // セッションに会員情報を格納
                        header('Location: 10_1kanri.php');  // 管理者ページにリダイレクト
                        exit; 
                    } else {
                        $errs[] = '管理者の権限がありません。';  // 権限がない場合のエラーメッセージ
                    }
                } else {
                    $errs[] = 'メールアドレスまたはパスワードに誤りがあります。';  // ログイン情報が間違っている場合
                }
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset = "utf-8">  <!-- 文字エンコードをUTF-8に指定 -->
    <link href="./css/LoginStyle.css" rel="stylesheet">  <!-- ログインページ用のスタイルシート -->
    <title>管理者ログイン</title>  <!-- ページタイトル -->
</head>
<body>
    <?php include "header.php"; ?>  <!-- 共通のヘッダーをインクルード -->

    <h1>管理者ログイン</h1>
    <p>以下の項目を入力し、ログインボタンをクリックしてください</p>

    <div class="center-wrapper">
        <div class="center">
            <!-- ログインフォーム -->
            <form action = "" method="POST">
                <table>
                    <!-- メールアドレス入力欄 -->
                    <tr> 
                        <td>会員ID(メールアドレス)</td> 
                        <td> 
                            <input type = "email" name="email" required autofocus value="<?= htmlspecialchars($email ?? '') ?>"> 
                        </td> 
                    </tr> 

                    <!-- パスワード入力欄 -->
                    <tr> 
                        <td>パスワード </td> 
                        <td> 
                            <input type = "password" name="password" required> 
                        </td> 
                    </tr> 

                    <!-- ログインボタン -->
                    <tr class="btn-container"> 
                        <td colspan="2"> 
                            <input type = "submit" value="ログイン" class = "btn"> 
                        </td> 
                    </tr> 

                    <!-- エラーメッセージの表示 -->
                    <tr>
                        <td colspan="2">
                            <?php foreach($errs as $e) : ?>
                                <span style = "color:red"><?= $e ?></span>
                                <br>
                            <?php endforeach; ?>
                        </td>
                    </tr>
                </table> 
            </form>
        </div>
    </div>
</body>
</html>
