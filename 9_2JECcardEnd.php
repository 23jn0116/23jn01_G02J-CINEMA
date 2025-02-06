<?php
// 必要なDAOクラスを読み込む
require 'helpers/JECcardDAO.php';  // JECカード情報のDAO
require_once 'helpers/KaiinDAO.php'; // 会員情報のDAO

// セッションを開始
session_start();
$kessai = 0;  // 決済フラグ（初期値は0）

// セッションから会員情報を取得
$kaiin = !empty($_SESSION['kaiin']) ? $_SESSION['kaiin'] : null;

// 決済フラグがセッションに保存されている場合、取得しリセット
if(isset($_SESSION['kessai'])){
    $kessai = $_SESSION['kessai'];  // 決済フラグを取得
    $_SESSION['kessai'] = 0;         // セッション内の決済フラグをリセット
}

// 会員情報が取得できているか確認
if ($kaiin && isset($kaiin->kno)) {
    $kno = $kaiin->kno;  // 会員番号を取得
} else {
    // 会員情報が正しく取得できない場合、エラーメッセージを表示しログインページへリダイレクト
    echo "<p style='color:red;'>会員情報が正しく取得できませんでした。再度ログインしてください。</p>";
    header("Location: 1_2login.php"); // ログインページにリダイレクト
    exit;
}

// JECカードDAOのインスタンスを作成し、カード情報を取得
$jeccardDAO = new JECcardDAO();
$jeccard = $jeccardDAO->getJECcard($kno); // 会員ID（kno）を使ってJECカード情報を取得

// 最新の残高を取得
$zandaka = $jeccard->zandaka; // JECカードの残高
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <link href="css/JECcard.css" rel="stylesheet">
    <title>チャージ完了</title>
</head>
<body>
    <!-- ヘッダーのインクルード -->
    <?php include 'header2.php'; ?>

    <!-- チャージ完了メッセージ -->
    <h1>チャージが完了しました。</h1>

    <!-- 現在の残高を表示 -->
    <h1>現在の残高: ￥<?php echo htmlspecialchars($zandaka); ?></h1>

    <!-- リンクを提供して、ユーザーを他のページに誘導 -->
    <p><a href="1_1index2.php">トップページへ</a></p>
    <p><a href="9_1mypage.php">マイページへ</a></p>

    <!-- 決済フラグが1の場合にのみ表示されるリンク -->
    <?php if($kessai == 1): ?>
        <p><a href="7_kessai.php">決済へ</a></p>
    <?php endif ?>
</body>
</html>
