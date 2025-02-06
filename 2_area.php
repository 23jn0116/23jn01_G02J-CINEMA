<?php
    require 'helpers/AreaDAO.php'; // AreaDAOをインクルードして、エリア情報の取得を行う
    if (isset($_GET['mcode'])) {
        $mcode = $_GET['mcode'];  // mcodeがURLのパラメータとして渡されている場合、変数に格納
    } else {
        $mcode = null;  // mcodeが渡されていない場合はnullを設定
    }

    // セッションが開始されていない場合は開始
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
        $_SESSION['purchase_complete'] = false;  // 購入完了状態をfalseに初期化
    }

    $areaDAO = new AreaDAO(); // AreaDAOのインスタンスを作成
    $results = [];  // 検索結果を格納する配列
    $area_list = $areaDAO->get_area();  // エリア情報を取得
    
    // エリア検索が指定されている場合
    if (isset($_GET['area'])) {
        $keyword = $_GET['area'];  // 検索ワードを取得
        $data = $areaDAO->get_area_by_keyword($keyword);  // キーワードでエリア情報を取得
        
        // 取得したエリア情報から検索ワードが含まれているものを結果として格納
        foreach ($data as $item) {
            if (stripos($item->areaname, $keyword) !== false) {
                $results[] = htmlspecialchars($item->areaname);  // エリア名をhtmlspecialcharsでエスケープして格納
            }
        }
    } else {
        $area_list = $areaDAO->get_area();  // 検索ワードがない場合は全エリア情報を取得
    }
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>地域検索</title>
    <link href="css/style.css" rel="stylesheet">  <!-- CSSファイルの読み込み -->
</head>
<body>
    <?php if (isset($_SESSION['kaiin'])) { 
        include "header2.php";  // 会員のヘッダーを表示
    } else { 
        include "header.php";  // 非会員のヘッダーを表示
    } ?>
    
    <div class="content">
        <h1>地域検索</h1>
        <form method="GET" action="">  <!-- GETメソッドで検索フォームを送信 -->
            <table style="margin: 0 auto; text-align: center;">
                <tr>
                    <td>
                        <input type="text" name="area" placeholder="地域検索" style="padding: 15px; width: 400px; font-size:20px; text-align:center;" value="<?php echo isset($keyword) ? htmlspecialchars($keyword) : ''; ?>">
                    </td>
                    <td>
                        <input type="submit" class="m-button" name="search" value="検索" style="padding: 15px; width: 200px;">
                    </td>
                </tr>
            </table>
        </form>

        <?php if (count($results) > 0): ?>  <!-- 検索結果が1件以上の場合 -->
            <div class="button-container">
                <form action="3_moviesearch.php" method="POST">
                <?php if($mcode != null):?>
                    <input type="hidden" name="mcode" value="<?= $mcode ?>"> <!-- mcodeが存在する場合はhiddenで送信 -->
                <?php endif;?>
                    <?php foreach ($results as $result): ?>
                        <button type="submit" class="movie-button" name="areaname" value="<?= $result ?>"><?= $result ?></button>  <!-- 検索結果をボタンとして表示 -->
                    <?php endforeach; ?>
                </form>
            </div>
        <?php elseif (isset($keyword)): ?>  <!-- 検索結果が0件で、検索ワードが指定されている場合 -->
            <p>結果が見つかりませんでした。</p>
            <div class="button-container">
                <form action="3_moviesearch.php" method="POST">
                    <?php if($mcode != null):?>
                    <input type="hidden" name="mcode" value="<?= $mcode ?>"> <!-- mcodeが存在する場合はhiddenで送信 -->
                    <?php endif;?>
                    <?php foreach ($area_list as $area): ?>  <!-- すべてのエリア情報をボタンとして表示 -->
                        <button type="submit" class="movie-button" name="areaname" value="<?= htmlspecialchars($area->areaname) ?>"><?= htmlspecialchars($area->areaname) ?></button>
                    <?php endforeach; ?>
                </form>
            </div>
        <?php endif; ?>

        <?php if (empty($results) && empty($keyword)): ?>  <!-- 検索結果がない場合、または検索ワードが指定されていない場合 -->
            <div class="button-container">
                <form action="3_moviesearch.php" method="POST">
                <?php if($mcode != null):?>
                    <input type="hidden" name="mcode" value="<?= $mcode ?>"> <!-- mcodeが存在する場合はhiddenで送信 -->
                <?php endif;?>
                    <?php foreach ($area_list as $area): ?>  <!-- すべてのエリア情報をボタンとして表示 -->
                        <button type="submit" class="movie-button" name="areaname" value="<?= htmlspecialchars($area->areaname) ?>"><?= htmlspecialchars($area->areaname) ?></button>
                    <?php endforeach; ?>
                </form>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
