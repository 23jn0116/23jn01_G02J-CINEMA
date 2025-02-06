<?php
require_once '../helpers/MovieDAO.php'; // 必要なMovieDAOファイルを読み込む

// POSTリクエストが送信された場合
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['tuika'])) {
        // フォームから送信された映画情報を取得
        $mname      = $_POST['mname'];        // 映画名
        $detail     = $_POST['detail'];       // 映画の詳細
        $koukaidate = $_POST['koukaidate'];   // 公開日
        $enddate    = $_POST['enddate'];      // 公開終了日
        $genrecode  = $_POST['genrecode'];    // ジャンルコード
        $sno        = $_POST['sno'];          // スクリーン番号
        $r          = $_POST['r'];            // レイティング
        $recommend  = $_POST['recommend'];    // おすすめ（0または1）
        $photoname  = $_POST['photoname'];    // ユーザーが入力した写真名

        // 画像ファイルの保存処理
        $target_dir = "../images/moviephoto/"; // 保存先ディレクトリ
        $imageFileType = strtolower(pathinfo($_FILES["photo"]["name"], PATHINFO_EXTENSION)); // ファイル拡張子を取得
        $target_file = $target_dir . $photoname . '.' . $imageFileType; // 保存するファイルのパス
        $uploadOk = 1;

        // 画像ファイルが実際に画像であるかをチェック
        $check = getimagesize($_FILES["photo"]["tmp_name"]);
        if ($check !== false) {
            echo "ファイルは画像です - " . $check["mime"] . ".";
            $uploadOk = 1;
        } else {
            echo "ファイルは画像ではありません。";
            $uploadOk = 0;
        }

        // ファイルのアップロード処理
        if ($uploadOk == 1) {
            if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
                echo "ファイル ". htmlspecialchars($photoname) . " がアップロードされました。";
                $photo = $photoname . '.' . $imageFileType; // 成功した場合、ファイル名を保存
            } else {
                echo "ファイルをアップロードできませんでした。";
                $photo = "";  // 失敗した場合、空文字に設定
            }
        }

        // エラーがない場合かつアップロードが成功した場合にデータベースに挿入
        if (empty($errs) && $uploadOk == 1) {
            // 新しいMovieオブジェクトを作成
            $movie = new Movie();
            $movieDAO = new MovieDAO;  // MovieDAOのインスタンスを生成
            $movie->mname       = $mname;       // 映画名
            $movie->detail      = $detail;      // 詳細
            $movie->koukaidate  = $koukaidate;  // 公開日
            $movie->enddate     = $enddate;     // 公開終了日
            $movie->genrecode   = $genrecode;   // ジャンルコード
            $movie->sno         = $sno;         // スクリーン番号
            $movie->r           = $r;           // レイティング
            $movie->recommend   = $recommend;   // おすすめフラグ
            $movie->photo       = $photo;       // 写真ファイル名
            $movieDAO->insert($movie); // MovieDAOを使ってデータベースに新しい映画情報を挿入
        }
    }
}

// GETリクエストで映画情報が送信されている場合
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // GETデータを取得し、HTMLエスケープ処理
    $mname = htmlspecialchars($_GET['mname'] ?? '');
    $detail = htmlspecialchars($_GET['detail'] ?? '');
    $koukaidate = htmlspecialchars($_GET['koukaidate'] ?? '');
    $enddate = htmlspecialchars($_GET['enddate'] ?? '');
    $genrecode = htmlspecialchars($_GET['genrecode'] ?? '');
    $sno = htmlspecialchars($_GET['sno'] ?? '');
    $r = htmlspecialchars($_GET['r'] ?? '');
    $recommend = htmlspecialchars($_GET['recommend'] ?? '');
    $photo = htmlspecialchars($_GET['photo'] ?? '');
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <title>映画追加完了</title>
    <style>
        .container {
            margin-top: 50px;
        }
        .movie-details {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>映画追加完了</h1>
        <div class="alert alert-success" role="alert">
            映画が正常に追加されました！ <!-- 映画追加成功メッセージ -->
        </div>
        <div class="movie-details">
            <h3>追加した映画の詳細</h3>
            <ul class="list-group">
                <li class="list-group-item"><strong>映画名: <?= $mname ?></strong></li>  <!-- 映画名 -->
                <li class="list-group-item"><strong>説明: <?= $detail ?></strong></li>  <!-- 映画説明 -->
                <li class="list-group-item"><strong>公開日: <?= $koukaidate ?></strong></li>  <!-- 公開日 -->
                <li class="list-group-item"><strong>公開終了日: <?= $enddate ?></strong></li>  <!-- 公開終了日 -->
                <li class="list-group-item"><strong>ジャンルコード: <?= $genrecode ?></strong></li>  <!-- ジャンルコード -->
                <li class="list-group-item"><strong>スクリーンナンバー: <?= $sno ?></strong></li>  <!-- スクリーン番号 -->
                <li class="list-group-item"><strong>レイティングシステム: <?= $r ?></strong></li>  <!-- レイティング -->
                <li class="list-group-item"><strong>おすすめ: <?= $recommend ?></strong></li>  <!-- おすすめフラグ -->
                <li class="list-group-item"><strong>写真: <img src="../images/moviephoto/<?= $photo ?>"></strong></li>  <!-- 映画写真 -->
            </ul>
        </div>

        <!-- 戻るボタン -->
        <button class="btn btn-primary mt-3" onclick="goBack()">戻る</button>
    </div>

    <script>
        // 戻るボタンをクリックした際に、映画追加画面へ遷移する処理
        function goBack() {
            window.location.href = '11_2movieSignup.php'; // 追加画面へのリンク
        }
    </script>

    <!-- 必要なJavaScriptライブラリの読み込み -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
