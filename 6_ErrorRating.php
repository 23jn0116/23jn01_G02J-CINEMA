<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>年齢制限エラー</title>
    
    <!-- Bootstrap CSSのインクルード -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Fontsのインクルード: RobotoとPoppinsフォントを使用 -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    
    <!-- カスタムCSSのインクルード -->
    <link rel="stylesheet" href="../css/ErrorHiKaiin.css">
</head>
<body>
    <!-- エラーメッセージを囲むコンテナ -->
    <div class="error-container">
        <div class="error-content text-center">
            <!-- エラーメッセージの見出し -->
            <h1 class="display-4 text-danger">購入できません</h1>
            
            <!-- 年齢制限についての説明 -->
            <p>この映画は<span class="fw-bold">年齢制限</span>が設定されています。</p>
            <p>指定された年齢に達していないため、購入手続きを進めることができません。</p>
            
            <!-- 追加ボタン: トップページへ戻る、他の映画を見る -->
            <div class="error-button">
                <a href="1_1index2.php" class="btn btn-black btn-custom">トップページへ戻る</a>
                <a href="2_area.php" class="btn btn-black btn-custom">他の映画を見る</a>
            </div>
        </div>
    </div>

    <!-- BootstrapのJavaScript（ポッパーとバンドルされたJS） -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
