<?php
    // MovieDAOクラスをインクルードして、映画のデータベースアクセスを行う
    require 'helpers/MovieDAO.php';
    $movieDAO = new MovieDAO();
    
    // おすすめ映画のリストを取得
    $recomennd_list = $movieDAO->get_recommend_movie();
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>J-CINNEMA</title>
    <link href="css/style.css" rel="stylesheet"> <!-- 外部CSSファイルのリンク -->
</head>
<body>
    <?php include "header.php"; ?>

    <!-- 管理者画面ボタン -->
    <div class="mypage-button-container">
        <button class="mypage-button" onclick="location.href='10_1kanrilogin.php'">管理者画面</button>
    </div>
    
    <!-- 映画を見るボタン -->
    <div class="button-container">
        <button class="movie-button" onclick="location.href='2_area.php'">映画を見る</button>
    </div>

    <!-- スライドショー -->
    <div class="carousel">
        <div class="carousel-inner">
            <?php foreach($recomennd_list as $recommend) : ?>
                <!-- 各映画の詳細ページに遷移するリンク -->
                <a href="2_area.php?mcode=<?= $recommend->mcode ?>">
                    <div class="carousel-item">
                        <!-- 映画の画像を表示 -->
                        <img src="../images/moviephoto/<?= $recommend->photo ?>" alt="<?= $recommend->mname ?>" />
                    </div>
                </a>
            <?php endforeach ?>
        </div>
        <!-- スライドの前後ボタン -->
        <div class="carousel-buttons">
            <button class="btn" onclick="prevSlide()">❮</button>
            <button class="btn" onclick="nextSlide()">❯</button>
        </div>
    </div>

    <script>
        // スライドの現在のインデックスを保持
        let currentIndex = 0;
        
        // スライドの表示を更新する関数
        function showSlide(index) {
            const slides = document.querySelector('.carousel-inner');
            const totalSlides = document.querySelectorAll('.carousel-item').length;
            
            // スライドの幅を正確に取得（余分な隙間を考慮）
            const slideWidth = document.querySelector('.carousel-item').offsetWidth + 20;

            // インデックスが範囲外の場合の処理
            if (index >= totalSlides) {
                currentIndex = 0; // 最後のスライドの後は最初に戻る
            } else if (index < 0) {
                currentIndex = totalSlides - 1; // 最初のスライド前は最後に戻る
            } else {
                currentIndex = index;
            }

            // スライドの表示位置を更新
            slides.style.transform = `translateX(-${currentIndex * slideWidth}px)`; 
        }

        // 次のスライドに移動する関数
        function nextSlide() {
            showSlide(currentIndex + 1);
        }

        // 前のスライドに移動する関数
        function prevSlide() {
            showSlide(currentIndex - 1);
        }

        // ページ読み込み後に最初のスライドを表示
        document.addEventListener('DOMContentLoaded', function() {
            showSlide(currentIndex);
        });
    </script>

    <!-- ログインと新規登録ボタン -->
    <div class="button-container">
        <button class="login-button" onclick="location.href='1_2login.php'">ログイン</button>
        <button class="signup-button" onclick="location.href='1_3signup.php'">新規登録</button>
    </div>
    
    <!-- フッター -->
    <footer>
        <p>© 2024 J-CINEMA</p>
    </footer>
</body>
</html>
