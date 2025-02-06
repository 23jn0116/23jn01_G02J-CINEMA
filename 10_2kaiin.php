<?php
require './helpers/KaiinDAO.php'; // DB接続ファイルを読み込み

// 登録者情報を取得する処理
$kaiinDAO = new KaiinDAO();
$kaiin_list = $kaiinDAO->get_member();
if (!$kaiin_list) {
    echo "ユーザーが見つかりません。";
    exit;
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登録者確認</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>登録者確認画面</h1>
    <?php foreach($kaiin_list as $kaiin): ?> <!-- ループを使って各登録者を表示 -->
        <p><strong>名前:</strong> <?=$kaiin->kananame ?></p>
        <p><strong>メールアドレス:</strong> <?=$kaiin->email?></p>
        <p><strong>電話番号:</strong> <?=$kaiin->tel?></p>
        <hr>
    <?php endforeach; ?>
    <button type="button" onclick="history.back()">戻る</button>
</body>
</html>

