<?php
// MovieDAOを読み込んで映画データの操作を行う準備
require './helpers/MovieDAO.php';

$movieDAO = new MovieDAO();

// GET パラメータで映画コードを取得
$mcode = isset($_GET['mcode']) ? $_GET['mcode'] : null;

// 映画情報をデータベースから取得
$movie = $movieDAO->get_mcode_movie($mcode);

// POSTリクエストが送信された場合の処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // フォームから送信されたデータを取得
    $mname = $_POST['mname'];
    $detail = $_POST['detail'];
    $koukaidate = $_POST['koukaidate'];
    $enddate = $_POST['enddate'];
    $genrecode = $_POST['genrecode'];
    $r = $_POST['r'];
    $recommend = isset($_POST['recommend']) ? 1 : 0;  // チェックボックスの状態を取得
    $photoname = $_POST['photoname']; // ユーザーが入力した写真名
    $photo = $_FILES['photo']['name']; // 送信された画像ファイル名

    // 画像ファイルが送信されている場合
    if ($photo) {
        // 拡張子を取得
        $extension = pathinfo($photo, PATHINFO_EXTENSION);
        
        // ユーザーが指定した名前と拡張子を結合して新しいファイル名を作成
        $newPhotoName = $photoname . '.' . $extension;

        // アップロードされた画像ファイルを指定した名前で保存
        move_uploaded_file($_FILES['photo']['tmp_name'], "../images/moviephoto/" . $newPhotoName); // 画像を保存
    } else {
        // 画像が送信されていない場合は、元の画像を使用
        $newPhotoName = $movie->photo;
    }

    // 映画情報をデータベースに更新
    $movieDAO->update_movie($mcode, $mname, $detail, $koukaidate, $enddate, $genrecode, $r, $recommend, $newPhotoName);

    // 編集完了後、リスト画面へリダイレクト
    header('Location: 11_4movieEditEnd.php');
    exit;
}

// 映画が見つからなかった場合はエラーメッセージを表示
if (!$movie) {
    echo "映画情報が見つかりません。";
    exit;
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <title>映画編集</title>
</head>
<body>
    <div class="container">
        <h1>映画編集</h1>
        <!-- 映画情報編集用のフォーム -->
        <form action="11_4movieEdit.php?mcode=<?= $movie->mcode ?>" method="POST" enctype="multipart/form-data">
            <!-- 映画名 -->
            <div class="form-group">
                <label for="mname">映画名</label>
                <input type="text" class="form-control" id="mname" name="mname" value="<?= $movie->mname ?>" required>
            </div>
            <!-- 映画説明 -->
            <div class="form-group">
                <label for="detail">映画説明</label>
                <textarea class="form-control" id="detail" name="detail" required><?= $movie->detail ?></textarea>
            </div>
            <!-- 公開日 -->
            <div class="form-group">
                <label for="koukaidate">公開日</label>
                <input type="date" class="form-control" id="koukaidate" name="koukaidate" value="<?= $movie->koukaidate ?>" required>
            </div>
            <!-- 公開終了日 -->
            <div class="form-group">
                <label for="enddate">公開終了日</label>
                <input type="date" class="form-control" id="enddate" name="enddate" value="<?= $movie->enddate ?>" required>
            </div>
            <!-- ジャンルコード -->
            <div class="form-group">
                <label for="genrecode">ジャンルコード</label>
                <input type="text" class="form-control" id="genrecode" name="genrecode" value="<?= $movie->genrecode ?>" required>
            </div>
            <!-- レイティングシステム -->
            <div class="form-group">
                <label for="r">レイティングシステム</label>
                <input type="text" class="form-control" id="r" name="r" value="<?= $movie->r ?>" required>
            </div>
            <!-- おすすめチェックボックス -->
            <div class="form-group">
                <label for="recommend">おすすめ</label>
                <input type="checkbox" id="recommend" name="recommend" <?= $movie->recommend ? 'checked' : '' ?>>
            </div>
            <!-- 映画の写真 -->
            <div class="form-group">
                <label for="photo">映画の写真</label>
                <input type="text" name="photoname" class="form-control" placeholder="写真の名前のみ入力" value="<?= $movie->photo ?>" required>
                <input type="file" class="form-control-file" id="photo" name="photo">
                <!-- 現在の画像を表示 -->
                <img src="./images/moviephoto/<?= $movie->photo ?>" alt="現在の写真" style="width: 100px;">
            </div>
            <!-- 更新ボタン -->
            <button type="submit" class="btn btn-success">更新</button>
            <!-- 戻るボタン -->
            <a href="11_1movieView.php" class="btn btn-secondary">戻る</a>
        </form>
    </div>
</body>
</html>
