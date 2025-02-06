<?php
// セッションを開始
session_start();

// セッションの内容を空にする
$_SESSION = [];

// 現在のセッションの名前を取得
$session_name = session_name();

// セッションに関連するクッキーが存在する場合、クッキーを削除
if (isset($_COOKIE[$session_name])) {
    setcookie($session_name, '', time() - 3600);  // クッキーを過去の時間に設定して削除
}

// セッションを完全に破棄
session_destroy();
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <link href="./css/style.css" rel="stylesheet">
    <title>パスワード変更完了</title>
</head>
<body>
    <?php include 'header2.php'; ?> <!-- ヘッダーをインクルード -->
    
    <!-- パスワード変更完了のメッセージ -->
    <h1>パスワード変更が完了しました。</h1>

    <!-- ログインページへのリンク -->
    <p><a href="1_2login.php">ログイン</a></p>

</body>
</html>
