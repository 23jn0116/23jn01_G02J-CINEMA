<?php
require '../helpers/MovieDAO.php'; 

$movieDAO = new MovieDAO();  // MovieDAOのインスタンスを生成
$movie_list = $movieDAO->get_movie();  // 映画リストを取得

// セッションが開始されていない場合に開始
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"> <!-- BootstrapのCSS -->
    <title>映画リスト</title>
</head>
<body>
    <div class="container">
        <!-- セッションメッセージ表示部分 -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-info"><?= $_SESSION['message'] ?></div>  <!-- メッセージを表示 -->
            <?php unset($_SESSION['message']); ?>  <!-- メッセージをセッションから削除 -->
        <?php endif; ?>
        
        <h1>映画リスト</h1>
        <div class="mb-3">
            <!-- 映画追加ページへのリンクボタン -->
            <a href="11_2movieSignup.php" class="btn btn-primary">映画追加</a>
            <!-- 管理者ページへの戻るボタン -->
            <a href="10_1kanri.php" class="btn btn-secondary">戻る</a>
        </div>
        
        <!-- 映画リスト表示用のテーブル -->
        <table class="table">
            <thead>
                <tr>
                    <!-- テーブルのヘッダー -->
                    <th>映画コ｜ド</th>
                    <th>映画名</th>
                    <th>映画説明</th>
                    <th>公開日</th>
                    <th>公開終了日</th>
                    <th>ジャンルコ｜ド</th>
                    <th>レイティングシステム</th>
                    <th>おすすめ</th>
                    <th>写真</th>
                    <th>編集</th>
                    <th>削除</th>
                </tr>
            </thead>
            <tbody>
                <!-- 映画リストがある場合、映画データを表示 -->
                <?php if (isset($movie_list) && count($movie_list) > 0): ?>
                    <?php foreach ($movie_list as $movie): ?>
                        <tr>
                            <td><?= $movie->mcode ?></td>  <!-- 映画コード -->
                            <td><?= $movie->mname ?></td>  <!-- 映画名 -->
                            <td><?= $movie->detail ?></td>  <!-- 映画の説明 -->
                            <td><?= $movie->koukaidate ?></td>  <!-- 公開日 -->
                            <td><?= $movie->enddate ?></td>  <!-- 公開終了日 -->
                            <td><?= $movie->genrecode ?></td>  <!-- ジャンルコード -->
                            <td><?= $movie->r ?></td>  <!-- レイティングシステム -->
                            <td><?= $movie->recommend ? 'おすすめ' : '非推奨' ?></td>  <!-- おすすめ表示 -->
                            <td><img src="../images/moviephoto/<?= $movie->photo ?>" alt="<?= $movie->mname ?>" style="width: 100px;"></td>  <!-- 映画写真 -->
                            <td><a href="11_4movieEdit.php?mcode=<?= $movie->mcode ?>" class="btn btn-warning">編集</a></td>  <!-- 編集ボタン -->
                            <td>
                                <!-- 削除ボタン、モーダルを開くトリガー -->
                                <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#deleteModal" data-mcode="<?= $movie->mcode ?>" data-mname="<?= $movie->mname ?>">
                                    削除
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- データがない場合のメッセージ -->
                    <tr>
                        <td colspan="13">データがありません</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- 削除確認モーダル -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">映画の削除確認</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>本当にこの映画を削除してもよろしいですか？</p>
                    <p id="movieName"></p>  <!-- 削除対象の映画名を表示 -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">キャンセル</button>
                    <!-- 削除確認フォーム -->
                    <form id="deleteForm" action="11_3movieDelete.php" method="POST">
                        <input type="hidden" name="mcode" id="movieCode">  <!-- 映画コードを隠しフィールドにセット -->
                        <button type="submit" class="btn btn-danger">削除</button>  <!-- 削除ボタン -->
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- 必要なJavaScriptライブラリの読み込み -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        // 削除モーダルが開かれるときに、映画コードと映画名をモーダルにセットする
        $('#deleteModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);  // モーダルを開いたボタン
            var mcode = button.data('mcode');  // 映画コード
            var mname = button.data('mname');  // 映画名

            var modal = $(this);
            modal.find('#movieCode').val(mcode);  // モーダル内のフォームに映画コードをセット
            modal.find('#movieName').text(mname);  // 映画名をモーダルに表示
        });
    </script>
</body>
</html>
