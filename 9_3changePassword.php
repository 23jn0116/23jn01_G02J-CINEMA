<?php
// セッションを開始
session_start();

// 必要なDAOクラスを読み込む
require 'helpers/KaiinDAO.php';

// ユーザー情報を取得するためのDAOクラスのインスタンスを作成
$kaiinDAO = new KaiinDAO();

// セッションから会員情報と会員IDを取得
$kaiin = $_SESSION['kaiin']; // 会員情報
$kno   = $_SESSION['id'];    // 会員ID
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- BootstrapのCSSを読み込む -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- 独自のCSSファイルを読み込む -->
    <link href="css/pass.css" rel="stylesheet">
    <title>パスワード変更</title>
</head>
<body>
    <div class="container">
        <!-- ページタイトル -->
        <h1>パスワード変更</h1>
        
        <!-- 注意書き：パスワードを変更するとログアウトすることを知らせるメッセージ -->
        <font color="red"><p>※パスワードを変更した場合、ログアウト処理を行います。</p></font>
        
        <!-- セッションメッセージを表示（もしあれば） -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-info"><?= $_SESSION['message'] ?></div>
            <?php unset($_SESSION['message']); // メッセージを表示後にセッションから削除 ?>
        <?php endif; ?>

        <!-- パスワード変更フォーム -->
        <form action="9_3updatePassword.php" method="POST">
            <!-- 現在のパスワード入力フィールド -->
            <div class="form-group">
                <label for="current_password">現在のパスワード</label>
                <input type="password" class="form-control" id="current_password" name="current_password" required>
            </div>
            
            <!-- 新しいパスワード入力フィールド -->
            <div class="form-group">
                <label for="new_password">新しいパスワード</label>
                <input type="password" class="form-control" id="new_password" name="new_password" required>
            </div>
            
            <!-- 新しいパスワード確認用入力フィールド -->
            <div class="form-group">
                <label for="confirm_password">新しいパスワード（確認）</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>
            
            <!-- パスワード変更ボタン -->
            <button type="submit" class="btn btn-primary">パスワード変更</button>
        </form>
        
        <!-- マイページへ戻るボタン -->
        <a href="9_1mypage.php" class="btn btn-secondary mt-3">戻る</a>
    </div>

    <!-- 必要なJSライブラリを読み込む（Bootstrapのため） -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
