<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>退会手続き完了</title>
    <link href="css/style.css" rel="stylesheet">
    <style>
        /* 追加のスタイリング */
        body {
            font-family: Arial, sans-serif;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 80%;
            max-width: 800px;
            margin: 50px auto;
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #ff6347;
            font-size: 2.5em;
            text-align: center;
        }

        p {
            font-size: 1.2em;
            line-height: 1.5;
            margin-bottom: 20px;
            text-align: center;
            color: #333;
        }

        .button-container {
            text-align: center;
        }

        .btn {
            background-color: #ffb300;
            color: white;
            padding: 15px 30px;
            font-size: 1.2em;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: black;
        }
    </style>
</head>
<body>
    <?php include "header.php"; ?>  <!-- ヘッダーの表示（任意） -->

    <div class="container">
        <h1>退会手続きが完了しました</h1>
        <p>ご利用いただきありがとうございました。</p>
        
        <p>あなたのアカウントは正常に退会処理されました。再度ご利用される際は、もう一度新規登録をお願いします。</p>
        
        <p>またのご利用をお待ちしております。</p>

        <div class="button-container">
            <a href="index.php" class="btn">トップページに戻る</a>
        </div>
    </div>
</body>
</html>
