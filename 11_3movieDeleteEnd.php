<?php
// セッション開始
session_start();
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <title>削除完了</title>
</head>
<body>
    <div class="container">
        <h1>削除完了</h1>
        
        <!-- 削除メッセージ -->
            <div class="alert alert-success">
                <p>削除に成功しました。</p>
            </div>
        
        <!-- 戻るボタン -->
        <a href="11_1movieView.php" class="btn btn-primary">映画リストに戻る</a>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
