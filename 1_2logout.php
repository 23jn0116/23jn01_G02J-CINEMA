<?php
    session_start(); // セッションを開始（セッション変数へのアクセスを可能にする）

    // セッション変数をすべて空にする
    $_SESSION = [];

    // セッション名を取得
    $session_name = session_name();

    // セッション用のクッキーが存在する場合、そのクッキーを削除する
    if(isset($_COOKIE[$session_name])){
        setcookie($session_name, '', time() - 3600); // クッキーの有効期限を過去に設定して削除
    }

    // セッションを破棄
    session_destroy();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset = "utf-8"> <!-- 文字コードをUTF-8に設定 -->
    <title>ログアウト完了</title> <!-- ページタイトル -->
</head>
<body>
    <?php include 'header.php'; ?> <!-- 共通ヘッダーを読み込み -->
    <link href="css/style.css" rel="stylesheet"> <!-- 外部CSSをリンク -->
    <h1>ログアウトが完了しました。</h1> <!-- ログアウト完了メッセージ -->
    <p><a href = "index.php">トップページへ</a></p> <!-- トップページへのリンク -->
</body>
</html>
