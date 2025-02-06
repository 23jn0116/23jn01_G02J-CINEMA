<?php 
    session_start(); // セッションを開始し、セッション情報を利用可能にする
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset = "utf-8"> <!-- 文字コードをUTF-8に設定 -->
    <link href="./css/style.css" rel="stylesheet"> <!-- 外部CSSをリンク -->
    <title>ログイン完了</title> <!-- ページタイトル -->
</head>
<body>
    <?php include 'header2.php'; ?> <!-- 共通ヘッダーを読み込み -->
    <h1>ログインが完了しました。</h1> <!-- ログイン完了メッセージ -->
    <p>ようこそJ-CINEMAへ</p> <!-- サービスのウェルカムメッセージ -->

    <!-- セッションに料金情報が設定されている場合、料金ページへのリンクを表示 -->
    <?php if(isset($_SESSION['ryokin'])):?>
        <p><a href = "6_ryokin.php">料金ページへ</a></p>
    <?php endif; ?>

    <!-- トップページへのリンクを表示 -->
    <p><a href = "1_1index2.php">トップページへ</a></p>
</body>
</html>
