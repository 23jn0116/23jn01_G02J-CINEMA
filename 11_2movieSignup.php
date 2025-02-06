<?php
require_once 'helpers/MovieDAO.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['tuika'])) {
        // フォームから送信されたデータを取得
        $mname      = $_POST['mname'];      // 映画名
        $detail     = $_POST['detail'];     // 映画の説明  
        $koukaidate = $_POST['koukaidate']; // 公開日
        $enddate    = $_POST['enddate'];    // 公開終了日
        $genrecode  = $_POST['genrecode'];  // ジャンルコード
        $r          = $_POST['r'];          // レイティングシステム
        $recommend  = $_POST['recommend'];  // おすすめ
        $photoname  = pathinfo($_POST['photoname'], PATHINFO_FILENAME); // 拡張子を除いたファイル名を取得

        // 画像ファイルの保存処理
        $target_dir = "./images/moviephoto/"; // 保存するディレクトリ
        $imageFileType = strtolower(pathinfo($_FILES["photo"]["name"], PATHINFO_EXTENSION)); // 画像の拡張子を取得
        $target_file = $target_dir . $photoname . '.' . $imageFileType; // 入力された名前でファイル名を設定
        $uploadOk = 1;

        // 画像ファイルのチェック
        $check = getimagesize($_FILES["photo"]["tmp_name"]);
        if ($check !== false) {
            echo "ファイルは画像です - " . $check["mime"] . ".";
            $uploadOk = 1;
        } else {
            echo "ファイルは画像ではありません。";
            $uploadOk = 0;
        }

        // ファイルをアップロード
        if ($uploadOk == 1) {
            if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
                echo "ファイル ". htmlspecialchars($photoname) . " がアップロードされました。";
                $photo = $photoname . '.' . $imageFileType; // ファイル名を完全な形式で保存
            } else {
                echo "ファイルをアップロードできませんでした。";
                $photo = "";
            }
        }

        if (empty($errs) && $uploadOk == 1) {
            // 映画データを新規作成
            $movie = new Movie();
            $movieDAO = new MovieDAO(); 
            $movie->mname       = $mname;
            $movie->detail      = $detail;
            $movie->koukaidate  = $koukaidate;
            $movie->enddate     = $enddate;
            $movie->genrecode   = $genrecode;
            $movie->r           = $r;
            $movie->recommend   = $recommend;
            $movie->photo       = $photoname . '.' . $imageFileType; // ファイル名を完全な形式で保存
            $movieDAO->insert($movie); // データベースに挿入
            header('Location: 11_2movieSignupEnd.php?' . http_build_query([
                'mname' => $movie->mname,
                'detail' => $movie->detail,
                'koukaidate' => $movie->koukaidate,
                'enddate' => $movie->enddate,
                'genrecode' => $movie->genrecode,
                'r' => $movie->r,
                'recommend' => $movie->recommend,
                'photo' => $photo, // ここでファイル名を渡す
            ]));
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <title>映画追加画面</title>
    <style>
        .container {
            margin-top: 50px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>映画を追加</h1>
        <form id="movieForm" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="genre">映画名</label>
                <input type="text" name="mname" class="form-control" placeholder="映画名を入力" required>
            </div>
            <div class="form-group">
                <label for="genre">説明</label>
                <textarea name="detail" class="form-control" rows="10" cols="50" placeholder="説明を入力"></textarea>
            </div>
            <div class="form-group">
                <label for="releaseYear">公開日</label>
                <input type="date" name="koukaidate" class="form-control" placeholder="公開日を入力" required>
            </div>
            <div class="form-group">
                <label for="releaseYear">公開終了日</label>
                <input type="date" name="enddate" class="form-control" placeholder="公開終了日を入力" required>
            </div>
            <div class="form-group">
                <label for="description">ジャンルコード</label>
                <input type="number" name="genrecode" class="form-control" placeholder="ジャンルコードを入力" min=1>
            </div>
            <div class="form-group">
                <label for="description">レイティングシステム</label>
                <input type="number" name="r" class="form-control" placeholder="レイティングシステムを入力" required>
            </div>
            <div class="form-group">
                <label for="description">おすすめ</label>
                <input type="number" name="recommend" class="form-control" placeholder="0,1（1:おすすめ,0:おすすめじゃない）を入力" required min=0 max=1>
            </div>
            <div class="form-group">
                <label for="description">写真</label>
                <input type="text" name="photoname" class="form-control" placeholder="写真の名前のみ入力" required>
                <input type="file" name="photo" class="form-control" placeholder="写真" required>
            </div>
            <button type="submit" name="tuika" class="btn btn-primary">追加</button> <button type="reset" class="btn btn-primary">リセット</button>
        </form>
        <p><a class="btn btn-primary" href="11_1movieView.php">戻る</a></p>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
