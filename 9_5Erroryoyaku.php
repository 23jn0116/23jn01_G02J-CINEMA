<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>予約済み映画</title>
    <!-- BootstrapのCSSをインクルードして、デザインを整える -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/yoyaku.css">
</head>
<body>
    <?php include "header.php"; ?>

    <div class="container">
        <h1>予約いただいている映画はございません。</h1> 
        <button class="home-button" onclick="location.href='1_1index2.php'">ホームへ戻る</button> <!-- ホームページに戻るためのボタン。クリックすると、1_1index2.phpに遷移する -->
    </div>

    <!-- BootstrapのJavaScriptファイルをインクルードして、インタラクティブな機能をサポート -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
