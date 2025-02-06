<?php
    // 必要なクラスをインクルード
    require_once './helpers/MovieDAO.php';
    session_start();
    $_SESSION['purchase_complete'] = false; // 購入完了状態を初期化
    
    // MovieDAOインスタンスを作成
    $movieDAO = new MovieDAO();
    $results = []; // 検索結果を格納する配列
    $movie = $movieDAO->get_movie(); // 全ての映画データを取得
    $area = $_POST['areaname']; // 選択されたエリアを取得
    $_SESSION['areaname'] = $area; // エリア名をセッションに保存

    // 映画コードがPOSTで送信された場合、その値を取得し、リダイレクト
    if (isset($_POST['mcode']) != null) {
        $mcode = $_POST['mcode'];

        // 映画コードをGETパラメータとして付加してリダイレクト
        header("Location: 4_date.php?mcode=" . urlencode($mcode));
        exit;
    }
    
    // 検索キーワードがGETで送信された場合、そのキーワードを元に映画を検索
    if (isset($_GET['moviesearch'])) {
        $keyword = $_GET['moviesearch']; // 検索キーワードを取得
        $data = $movieDAO->get_movie_by_keyword($keyword); // キーワードに基づく映画データを取得
        
        // キーワードが映画名に含まれている場合、検索結果に追加
        foreach ($data as $item) {
            if (stripos($item->mname, $keyword) !== false) { 
                $results[] = $item;
            }
        }
    } else {
        $movie = $movieDAO->get_movie(); // 全映画データを再取得（キーワード検索なし）
    }

    // 映画コードがPOSTで送信されている場合、その映画の詳細情報を取得
    if (isset($_POST['mcode'])) {
        $mcode = $_POST['mcode']; // 映画コードを取得
        $seachmovie = $movieDAO->get_mcode_movie($mcode); // 映画コードに基づく映画データを取得
    }

    // 映画コードが存在すれば、その公開日を取得
    if (isset($mcode)) {
        $koukaidates = $movieDAO->get_koukaidate($mcode); // 映画コードを渡して公開日データを取得
    } else {
        $koukaidates = [];  // 映画コードが無ければ空配列をセット
    }

    // 公開日がある場合に表示する処理
    $day1 = isset($koukaidates[0]['koukaidate']) ? $koukaidates[0]['koukaidate'] : '公開日なし';
    $day2 = isset($koukaidates[1]['koukaidate']) ? $koukaidates[1]['koukaidate'] : '公開日なし';
    $day3 = isset($koukaidates[2]['koukaidate']) ? $koukaidates[2]['koukaidate'] : '公開日なし';

?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>映画検索</title>
    <link href="./css/style.css" rel="stylesheet">
    <style>
        /* コンテナのスタイル */
        .container {
            text-align: center;
            margin: 20px;
        }

        /* 映画リンクのスタイル */
        .movie-link {
            color: #f2f2f2;
            text-decoration: none;
            font-size: 1.2em;
        }

        /* 映画選択のカードレイアウト */
        .movie-selection {
            display: flex;
            flex-wrap: wrap; /* アイテムを折り返す */
            justify-content: center; /* アイテムを中央揃え */
            gap: 20px; /* 画像間の隙間 */
        }

        .movie-item {
            text-align: center;
            width: 250px; /* 一つのアイテムの幅 */
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            height: 350px; /* アイテムの高さを一定に保つ */
            border: 3px solid yellow; /* 黄色の外枠 */
            border-radius: 10px; /* 角を丸くする */
            padding: 10px; /* 内側の余白 */
            transition: transform 0.3s ease, box-shadow 0.3s ease; /* 変化のアニメーション */
        }

        /* ホバー時の効果 */
        .movie-item:hover {
            transform: scale(1.05); /* ホバー時に少し大きくする */
            box-shadow: 0 0 10px rgba(255, 215, 0, 0.6); /* 黄色の影をつける */
        }

        /* 画像のスタイル */
        .movie-item img {
            width: 230px; /* 画像の幅を固定 */
            height: 230px; /* 画像の高さも固定 */
            object-fit: cover; /* 画像の縦横比を保ちながら収める */
            margin-bottom: 10px; /* 画像とタイトルの間にスペースを確保 */
        }

        /* 映画名のスタイル */
        .movie-item p {
            font-size: 1em; /* 映画名のフォントサイズを統一 */
            font-weight: bold;
            margin: 0;
            line-height: 1.4; /* テキストの行間を調整 */
        }

        @media screen and (max-width: 768px) {
            .movie-item {
                width: 200px;
                height: 320px;
            }

            .movie-link {
                font-size: 1em;
            }
        }

        @media screen and (max-width: 480px) {
            .movie-selection {
                justify-content: space-around;
            }
        }
    </style>
</head>
<body>
    <?php 
        // ヘッダーを会員かどうかで分ける
        if(isset($_SESSION['kaiin'])){ 
            include "header2.php"; 
        } else { 
            include "header.php"; 
        } 
    ?>
    <div class="container">
        <!-- 映画検索フォーム -->
        <div class="button-row">
            <form action="" method="GET">
                <input type="text" name="moviesearch" placeholder="映画検索" style="padding: 15px; width: 400px; font-size:20px; text-align:center;" value="<?php echo isset($keyword) ? htmlspecialchars($keyword) : ''; ?>">
                <input type="submit" class="search-button" name="kettei" value="検索" style="padding: 15px;  width: 200px;">
            </form>
        </div>

        <!-- 検索結果がある場合、映画を表示 -->
        <?php if (count($results) > 0): ?>
            <div class="movie-selection">
                <?php foreach($results as $result): ?>
                    <form action="4_date.php" method="GET" class="movie-item">
                        <a href="4_date.php?mcode=<?= $result->mcode ?>" class="movie-link">
                            <img src="../images/moviephoto/<?= $result->photo ?>" alt="<?= $result->mname ?>">
                            <p><?= $result->mname ?></p>
                        </a>
                    </form>
                <?php endforeach; ?>
            </div>
        <?php elseif (isset($keyword)): ?>
            <p>結果が見つかりませんでした。</p>
            <div class="movie-selection">
                <?php foreach($movie as $m): ?>
                    <form action="4_date.php" method="GET" class="movie-item">
                        <a href="4_date.php?mcode=<?= $m->mcode ?>" class="movie-link">
                            <img src="../images/moviephoto/<?= $m->photo ?>" alt="<?= $m->mname ?>">
                            <p><?= $m->mname ?></p>
                        </a>
                    </form>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- 検索結果がない場合、全ての映画を表示 -->
        <?php if (empty($results) && empty($keyword)): ?>
            <div class="button-container">
                <?php foreach($movie as $m): ?>
                    <form action="4_date.php" method="GET" class="movie-item">
                        <a href="4_date.php?mcode=<?= $m->mcode ?>" class="movie-link">
                            <img src="./images/moviephoto/<?= $m->photo ?>" alt="<?= $m->mname ?>">
                            <p><?= $m->mname ?></p>
                        </a>
                    </form>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
