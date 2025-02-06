<?php

    require_once 'helpers/KaiinDAO.php'; // 会員情報を扱うDAO
    require 'helpers/MovieDAO.php'; // 映画情報を扱うDAO
    
    // MovieDAOのインスタンスを作成し、映画推薦リストを取得
    $movieDAO = new MovieDAO();
    $recomennd_list = $movieDAO->get_recommend_movie(); // 映画推薦リストを取得
    
    // セッションが開始されていない場合はセッションを開始
    if(session_status() === PHP_SESSION_NONE){
        session_start();
        $_SESSION['purchase_complete'] = false; // 購入完了フラグを初期化
    }

    // セッションに会員情報がある場合は変数に格納、ない場合はログイン画面にリダイレクト
    if(!empty($_SESSION['kaiin'])){
        $kaiin = $_SESSION['kaiin'];
    } else {
        header("Location: index.php"); // ログインページへリダイレクト
        exit; // スクリプトを終了
    }
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>J-CINNEMA</title>
    <link href="css/style.css" rel="stylesheet"> <!-- 外部CSSをリンク -->
</head>

<body>
    <?php include "header2.php"; ?>
    <!-- 右上にマイページボタンを追加 -->
    <div class="mypage-button-container">
        <button class="mypage-button" onclick="location.href='9_1mypage.php'">マイページ</button>
    </div>

    <!-- 映画一覧へのリンクボタン -->
    <div class="button-container">
        <button class="movie-button" onclick="location.href='2_area.php'">映画を見る</button>
    </div>

     <!-- 映画推薦スライダーを表示するためのセクション -->
    <div class="carousel">
        <div class="carousel-inner">
            <!-- 推薦された映画リストを1つずつ表示 -->
            <?php foreach($recomennd_list as $recommend) : ?>
                <!-- 映画情報へのリンクと画像を表示 -->
                <a href="2_area.php?mcode=<?=$recommend->mcode?>"><div class="carousel-item"><img src="../images/moviephoto/<?= $recommend->photo ?>" alt="<? $recommend->mname ?>" width="250" height="auto"></div><a>
            <?php endforeach ?>
        </div>
        <div class="carousel-buttons">
            <!-- スライドを操作するためのボタン -->
            <button class="btn" onclick="prevSlide()">❮</button>
            <button class="btn" onclick="nextSlide()">❯</button>
        </div>
    </div>
    
    <script>
        let currentIndex = 0; // 現在のスライドのインデックス
        function showSlide(index) {
            const slides = document.querySelector('.carousel-inner');
            const totalSlides = document.querySelectorAll('.carousel-item').length;
            const slideWidth = slides.children[0].offsetWidth + 40; // paddingの分を考慮
            if (index >= totalSlides) {
                currentIndex = 0; // 最後のスライドの後は最初に戻る
            } else if (index < 0) {
                currentIndex = totalSlides - 1; // 最初のスライドの前は最後に戻る
            } else {
                currentIndex = index; // 次または前のスライドに移動
            }
            slides.style.transform = `translateX(-${currentIndex * slideWidth}px)`; // スライドの幅を基にスライド
        }
        function nextSlide() {
            showSlide(currentIndex + 1); // 次のスライドを表示
        }
        function prevSlide() {
            showSlide(currentIndex - 1); // 前のスライドを表示
        }
    </script>

    <!-- 会員の名前を表示 -->
    <h1><?=$kaiin->kananame?>さんようこそ</h1>
    <div class="button-container">
        <button class="signup-button" onclick="location.href='1_2logout.php'">ログアウト</button>
    </div>
    <footer>
        <p>© 2024 J-CINEMA</p>
    </footer>
</body>
</html>
