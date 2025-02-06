<?php
    require_once '../helpers/KaiinDAO.php';

    if(session_status() === PHP_SESSION_NONE){
        session_start();
    }

    if(!empty($_SESSION['kaiin'])){
        $kaiin = $_SESSION['kaiin'];
    }
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <title>管理者画面</title>
    <style>
        .sidebar {
            height: 100vh;
            background-color: #f8f9fa;
            padding: 20px;
            border-right: 2px solid #ddd;
        }
        .sidebar h2 {
            font-size: 1.5rem;
        }
        .sidebar p {
            font-weight: bold;
        }
        .sidebar .nav-link {
            padding: 10px 15px;
            font-size: 1.1rem;
            color: #007bff;
        }
        .sidebar .nav-link:hover {
            background-color: #e2e6ea;
        }
        .main-content {
            padding: 20px;
        }
        .main-content h1 {
            font-size: 2rem;
            margin-bottom: 30px;
        }
        .btn-primary {
            font-size: 1.1rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- サイドバー -->
            <nav class="col-md-3 col-lg-2 sidebar">
                <h2>管理者メニュー</h2>
                <p><?= $kaiin->kananame ?>さん</p>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="10_2kaiin.php">登録者確認</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="11_1movieView.php">映画追加・削除</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="11_5scheduleAdd.php">スケジュール追加・削除</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="1_2logout.php">ログアウト</a>
                    </li>
                </ul>
            </nav>

            <!-- メインコンテンツ -->
            <main class="col-md-9 ml-sm-auto col-lg-10 px-4 main-content">
                <h1>管理者ダッシュボード</h1>

                <!-- 各操作ボタン -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <a href="10_2kaiin.php" class="btn btn-primary btn-block">登録者確認</a>
                    </div>
                    <div class="col-md-6 mb-3">
                        <a href="11_1movieView.php" class="btn btn-primary btn-block">映画追加・削除</a>
                    </div>
                    <div class="col-md-6 mb-3">
                        <a href="11_5scheduleAdd.php" class="btn btn-primary btn-block">スケジュール追加・削除</a>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
