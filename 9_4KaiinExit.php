<?php
session_start();
require '../helpers/KaiinDAO.php';

// ユーザー情報を取得
$kaiinDAO = new KaiinDAO();
$kaiin = $_SESSION['kaiin'];
$kno = $_SESSION['id'];

// 退会確認後、退会処理を実行
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['confirm']) && $_POST['confirm'] == '1') {
        // 退会処理
        $kaiinDAO->delete($kno);
        
        // 退会後はセッションを破棄して、ログインページやトップページにリダイレクト
        session_destroy();
        header("Location: 9_4kaiinExitEnd.php");  // 退会完了ページにリダイレクト
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>退会申請</title>
    <link rel="stylesheet" href="../css/exit.css">
    <style>
        /* モーダルウィンドウのデザイン */
        .modal {
            display: none; /* 初期状態では非表示 */
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
            justify-content: center;
            align-items: center;
        }
        /* モーダルのコンテンツ */
        .modal-content {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            width: 400px;
            text-align: center;
        }
        .modal-footer button {
            padding: 10px 20px;
            margin: 5px;
            border: none;
            background-color: #4CAF50;
            color: white;
            font-size: 1.1em;
            cursor: pointer;
            border-radius: 5px;
        }
        .modal-footer button.cancel {
            background-color: #f44336;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <font color=#ffcc00><h1>退会申請</h1></font>
        </header>

        <section class="main-content">
            <h2>本当に退会しますか？</h2>
            <p>退会後は、サービスの利用ができなくなります。退会を続ける場合は、以下の確認事項を読んでください。</p>

            <form id="cancellation-form" action="" method="POST">
                <div class="warning">
                    <p>※退会後はアカウント情報、予約情報などのデータがすべて削除されます。復元できません。</p>
                </div>

                <font color=white><label for="reason">退会理由を教えてください（任意）</label></font>
                <textarea id="reason" name="reason" rows="4" placeholder="退会理由をお聞かせください"></textarea>

                <div class="checkbox-group">
                    <label>
                        <font color=white><input type="checkbox" id="confirm" name="confirm" value="1" required> 退会することを確認しました</font>
                    </label>
                </div>

                <div class="action-buttons">
                    <!-- 退会手続きを完了するボタン（モーダルで確認） -->
                    <button type="button" class="submit-btn" onclick="validateAndShowModal()">退会手続きを完了する</button>
                    <button type="button" class="cancel-btn" onclick="window.location.href='9_1mypage.php'">キャンセル</button>
                </div>
            </form>
        </section>

        <footer>
            <p>&copy; 2024 J-CINEMA</p>
        </footer>
    </div>

    <!-- モーダルウィンドウ -->
    <div id="confirmation-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>退会の確認</h2>
            </div>
            <font color=red>本当に退会しますか？この操作は取り消せません。</font>
            <div class="modal-footer">
                <button class="confirm" onclick="submitForm()">退会する</button>
                <button class="cancel" onclick="closeModal()">キャンセル</button>
            </div>
        </div>
    </div>

    <script>
        // チェックボックスがチェックされているかどうかを確認する関数
        function validateAndShowModal() {
            const confirmCheckbox = document.getElementById('confirm');
            if (confirmCheckbox.checked) {
                showModal();
            } else {
                alert('退会することを確認するチェックボックスを押してください。');
            }
        }

        // モーダルを表示する関数
        function showModal() {
            document.getElementById('confirmation-modal').style.display = 'flex';
        }

        // モーダルを閉じる関数
        function closeModal() {
            document.getElementById('confirmation-modal').style.display = 'none';
        }

        // 退会処理を完了する関数（フォームを送信）
        function submitForm() {
            // モーダルを閉じる
            closeModal();
            // フォームを送信する
            document.getElementById('cancellation-form').submit();
        }
    </script>
</body>
</html>
