<?php
    // KaiinDAO と MovieDAO を読み込む
    require_once 'helpers/KaiinDAO.php'; // 会員情報を取り扱うDAO
    require 'helpers/MovieDAO.php';     // 映画情報を取り扱うDAO
    
    // MovieDAO のインスタンスを作成
    $movieDAO = new MovieDAO();
    
    // おすすめ映画のリストを取得
    $recomennd_list = $movieDAO->get_recommend_movie();

    // セッションが開始されていなければ開始
    if(session_status() === PHP_SESSION_NONE){
        session_start();
    }

    // セッション内に 'kaiin' が設定されていれば、会員情報を取得
    if(!empty($_SESSION['kaiin'])){
        $kaiin = $_SESSION['kaiin'];
    }
?>
<!DOCTYPE html>
<html lang = "ja">
<head>
    <meta charset = "utf-8">
    <title>JecShopping - mypage</title>
    <link href = "css/style.css" rel="stylesheet"> <!-- 外部CSSをリンク -->
</head>
<body>
    <?php include "header2.php"; ?> <!-- ヘッダーを読み込む -->

<!DOCTYPE html>
<html>
<head>
    <meta charset = "utf-8">
    <link href = "css/mypage.css" rel = "stylesheet"> <!-- mypage.css をリンク -->
    <title>mypage</title>
</head>
<body>

<h1><strong><?=$kaiin->kananame?>様</strong></h1> <!-- 会員名を表示 -->
<p>いつもご利用ありがとうございます😊</p>

<!-- ボタンのコンテナ -->
<div class="button-container">
    <!-- 各ボタンのリンクを設定 -->
    <button class="movie-button" onclick="location.href='9_5yoyaku.php'">予約済み映画</button> <!-- 予約済み映画ページへのリンク -->
    <button class="movie-button" onclick="location.href='9_2JECcard.php'">日電カードチャージ</button> <!-- JECカードチャージページへのリンク -->
    <button class="movie-button" onclick="location.href='9_3changePassword.php'">パスワード変更</button> <!-- パスワード変更ページへのリンク -->
    <button class="movie-button" onclick="location.href='9_4kaiinExit.php'">退会申請</button> <!-- 退会申請ページへのリンク -->
    <button class="movie-button" onclick="location.href='1_1index2.php'">戻る</button> <!-- 戻るボタン -->
</div>
   
</body>
</html>
