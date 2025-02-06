<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログインエラー</title>
    
    <!-- Bootstrap CSS (Bootstrapのスタイルを読み込む) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Fonts: RobotoとPoppinsフォントを使用 -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    
    <!-- 外部CSS (ErrorHiKaiin.cssファイルを読み込む) -->
    <link rel="stylesheet" href="./css/ErrorHiKaiin.css">
</head>
<body>
    <!-- エラーメッセージを表示するためのコンテナ -->
    <div class="error-container">
        <div class="error-content">
            <!-- エラーメッセージ -->
            <h1 class="display-4">購入するには会員登録が必要です。</h1>
            <div class="error-button">
                <!-- ログインページへのリンク -->
                <a href="1_2login.php" class="btn btn-custom">ログインページへ進む</a>
                <!-- 会員登録ページへのリンク -->
                <a href="1_3signup.php" class="btn btn-custom">会員登録へ進む</a>
                <!-- トップページへのリンク -->
                <a href="index.php" class="btn btn-custom">トップページへ戻る</a>
            </div>
        </div>
    </div>

    <!-- 必要なBootstrapのJavaScript (ポップアップなどの動作に必要) -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
