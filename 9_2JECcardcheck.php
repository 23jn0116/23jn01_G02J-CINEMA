<?php
// 必要なDAOクラスを読み込む
require 'helpers/JECcardDAO.php';  // JECカード情報のDAO
require_once 'helpers/KaiinDAO.php'; // 会員情報のDAO

// セッションが開始されていない場合はセッションを開始
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// セッションから会員情報を取得
$kaiin = !empty($_SESSION['kaiin']) ? $_SESSION['kaiin'] : null;

// 会員情報（kno）が取得できているか確認
if ($kaiin && isset($kaiin->kno)) {
    $kno = $kaiin->kno;
} else {
    // 会員情報がない場合、エラーメッセージを表示しログインページにリダイレクト
    echo "<p style='color:red;'>会員情報が正しく取得できませんでした。再度ログインしてください。</p>";
    header("Location: 1_2login.php"); // ログインページにリダイレクト
    exit;
}

// POSTで送信されたチャージ金額を取得
$selectedcharge = isset($_POST['selectedcharge']) ? intval(str_replace('￥', '', $_POST['selectedcharge'])) : 0;

// チャージ金額が正の整数であることを確認
if ($selectedcharge <= 0) {
    echo "<p style='color:red;'>チャージ金額は正の整数でなければなりません。</p>";
    exit;
}

// JECカードDAOのインスタンスを作成し、カード情報を取得
$jeccardDAO = new JECcardDAO();
$jeccard = $jeccardDAO->getJECcard($kno); // 会員ID（kno）を使用してJECカード情報を取得

// チャージ処理（フォームが送信されたときのみ実行）
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['charge_button'])) {
    // チャージ金額（selectedcharge）を使って残高を更新
    if ($jeccardDAO->charge_insert($kno, $selectedcharge)) {
        // チャージ処理が成功した場合、新しい残高を取得
        $jeccard = $jeccardDAO->getJECcard($kno);
        $_SESSION['zandaka'] = $jeccard->zandaka; // 新しい残高をセッションに保存
        $_SESSION['charge_success'] = true; // チャージ成功フラグをセッションに保存
        header("Location: 9_2JECcardEnd.php"); // チャージ完了ページにリダイレクト
        exit;
    } else {
        // チャージ処理が失敗した場合
        echo "<p style='color:red;'>チャージ処理に失敗しました。再度お試しください。</p>";
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>JECcardcheck</title>
    <link href="css/JECcard.css" rel="stylesheet">
</head>
<body>
    <?php include "header2.php"; ?> <!-- ヘッダーを読み込む -->

    <!-- チャージ金額と現在の残高を表示 -->
    <h1>チャージ金額: ￥<?php echo htmlspecialchars($selectedcharge); ?></h1>
    <h1>現在の残高: ￥<?php echo htmlspecialchars($jeccard->zandaka); ?></h1>
    <h2>よろしければ、チャージを押してください。</h2>

    <!-- チャージ完了ボタンを表示 -->
    <div class="button-container">
        <!-- チャージ処理用のフォーム -->
        <form action="9_2JECcardcheck.php" method="POST">
            <input type="hidden" name="selectedcharge" value="￥<?php echo htmlspecialchars($selectedcharge); ?>"> <!-- チャージ金額を隠しフィールドとして送信 -->
            <button type="submit" name="charge_button" class="button">チャージ完了</button> <!-- チャージボタン -->
        </form>
    </div>
</body>
</html>
