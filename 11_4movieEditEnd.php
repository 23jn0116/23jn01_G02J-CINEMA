<?php
// セッション開始
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 完了メッセージをセッションに保存
if (!isset($_SESSION['change_message'])) {
    $_SESSION['change_message'] = '映画情報の変更が完了しました。';
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <title>変更完了</title>
</head>
<body>
    <div class="container">
        <h1>変更完了</h1>
        <div class="alert alert-success">
            <?php
            // セッションからメッセージを表示
            echo $_SESSION['change_message'];
            // メッセージを表示後、セッションから削除
            unset($_SESSION['change_message']);
            ?>
        </div>
        <a href="11_1movieView.php" class="btn btn-primary">映画リストに戻る</a>
    </div>
</body>
</html>
