<?php
require '../helpers/MovieDAO.php';

// セッション開始
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// MovieDAOのインスタンスを作成
$movieDAO = new MovieDAO();

// POSTデータの取得
if (isset($_POST['mcode'])) {
    $mcode = $_POST['mcode'];

    // 削除処理
    $deleteResult = $movieDAO->delete_movie($mcode);
}

// 映画リストページにリダイレクト
header('Location: 11_3movieDeleteEnd.php');
exit;
