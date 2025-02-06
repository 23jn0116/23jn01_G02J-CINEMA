<?php
session_start(); // セッションを開始

// エラーが表示された後にフラグをリセット
$_SESSION['purchase_complete'] = false;

// エラーメッセージを取得
$error = isset($_GET['error']) ? $_GET['error'] : '';
?>

<!DOCTYPE html>
<html lang="ja">

<!-- 外部CSS -->
    <link rel="stylesheet" href="css/ErrorKessai.css">

<head>
    <meta charset="utf-8">
    <title>決済エラー</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="alert-container">
    <h2>決済エラー</h2>
    <?php if ($error == 'paymentFailed'): ?>
        <!-- 決済処理に失敗した場合のエラーメッセージ -->
        <strong><p>決済処理に失敗しました。再度お試しください。</p></strong>
    <?php elseif ($error == 'updateFailed'): ?>
        <!-- 予約状況の更新に失敗した場合のエラーメッセージ -->
        <strong><p>予約状況の更新に失敗しました。後ほど再試行してください。</p></strong>
    <?php else: ?>
        <!-- 他のエラーが発生した場合のエラーメッセージ -->
        <strong><p>チケットは購入済みです。</p></strong>
    <?php endif; ?>
    <!-- トップページへのリンクボタン -->
    <a href="1_1index2.php" class="btn btn-primary">トップページへ戻る</a>
</div>

</body>
</html>
