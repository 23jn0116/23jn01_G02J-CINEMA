<?php
// セッションを開始
session_start();

// ユーザーがログインしていない場合、ログイン画面にリダイレクト
if (!isset($_SESSION['kaiin'])) {
    header('Location: 1_2login.php');  // ログインページにリダイレクト
    exit;
}

// ユーザー情報を取得するためのDAOインスタンスを作成
$kaiinDAO = new KaiinDAO();

// セッションからユーザー情報を取得
$kaiin = $_SESSION['kaiin'];
$kno   = $_SESSION['id']; // 会員ID
// データベースからユーザーのハッシュ化されたパスワードを取得
$pass  = $kaiinDAO->get_pass($kno); 

// フォームがPOSTメソッドで送信された場合
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // フォームから現在のパスワード、新しいパスワード、確認用パスワードを取得
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // 現在のパスワードがデータベースのものと一致するか確認
    if (!password_verify($current_password, $pass)) {
        // 一致しない場合、エラーメッセージを表示してパスワード変更ページにリダイレクト
        $_SESSION['message'] = '現在のパスワードが間違っています。';
        header('Location: 9_3changePassword.php');  // パスワード変更ページにリダイレクト
        exit;
    }

    // 新しいパスワードと確認用パスワードが一致するか確認
    if ($new_password !== $confirm_password) {
        // 一致しない場合、エラーメッセージを表示してパスワード変更ページにリダイレクト
        $_SESSION['message'] = '新しいパスワードと確認用パスワードが一致しません。';
        header('Location: 9_3changePassword.php');  // パスワード変更ページにリダイレクト
        exit;
    }

    // 新しいパスワードの強度を確認（例: 最低4文字）
    if (strlen($new_password) < 4) {
        // 条件を満たさない場合、エラーメッセージを表示
        $_SESSION['message'] = '新しいパスワードは４文字以上にしてください。'; 
        header('Location: 9_3changePassword.php');
        exit;
    }

    // パスワードをデータベースに保存
    $updateResult = $kaiinDAO->update_password($kno, $new_password);

    // パスワード更新が成功した場合
    if ($updateResult) {
        // 更新成功後、完了ページにリダイレクト
        header('Location: 9_3updatepasswordEnd.php');  
        exit;
    } else {
        // 更新に失敗した場合、エラーメッセージを表示
        $_SESSION['message'] = 'パスワードの更新に失敗しました。再度お試しください。';
        header('Location: 9_3changePassword.php');  // パスワード変更ページにリダイレクト
        exit;
    }
}
?>
