<?php
require_once 'helpers/KaiinDAO.php'; // 会員情報を管理するクラスを読み込み
require_once 'helpers/JECcardDAO.php'; // JECカード情報を管理するクラスを読み込み

$errs  = []; // エラーメッセージを格納する配列を初期化

// フォームが送信された場合の処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['touroku'])) { // 「登録」ボタンが押された場合のみ処理を実行
        $kname      = $_POST['kname'];    // 漢字名を取得
        $kananame   = $_POST['kananame']; // カナ名を取得
        $age        = $_POST['age'];      // 年齢を取得
        $seibetu    = $_POST['seibetu'];  // 性別を取得
        $email      = $_POST['email'];    // メールアドレスを取得
        $email2     = $_POST['email2'];   // 確認用メールアドレスを取得
        $password   = $_POST['pass'];     // パスワードを取得
        $password2  = $_POST['pass2'];    // 確認用パスワードを取得
        $tel1       = $_POST['tel1'];     // 電話番号1（市外局番）を取得
        $tel2       = $_POST['tel2'];     // 電話番号2（市内局番）を取得
        $tel3       = $_POST['tel3'];     // 電話番号3（加入者番号）を取得

        $kaiinDAO = new KaiinDAO(); // 会員情報管理クラスのインスタンスを作成
        $jeccardDAO = new JECcardDAO(); // JECカード情報管理クラスのインスタンスを作成

        // メールアドレスチェック
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { // メールアドレス形式を確認
            $errs['email'] = 'メールアドレスの形式が正しくありません。';
        }else if ($kaiinDAO->email_exists($email) === true) { // メールアドレスがすでに登録済みか確認
            $errs['email'] = 'このメールアドレスはすでに登録されています。';
        }else if($email != $email2){ // 確認用メールアドレスと一致するか確認
            $errs['email'] = 'メールアドレスが一致しません。';
        }

        // パスワードチェック
        if (!preg_match('/\A.{4,}\z/', $password)) { // パスワードが4文字以上か確認
            $errs['password'] = 'パスワードは4文字以上で入力してください。';
        } else if ($password !== $password2) { // 確認用パスワードと一致するか確認
            $errs['password'] = 'パスワードが一致しません。';
        }

        // 性別チェック（必須項目）
        if (!isset($seibetu) || $seibetu === '') {
            $errs['seibetu'] = '性別を選択してください。';
        }

        // 漢字名チェック（漢字のみ許可）
        if($kname != ""){ // 名前が空でない場合にのみチェック
            if (!preg_match('/^[一-龥々〆〤]+$/u', $kname)) {
                $errs['kname'] = '名前（漢字）は漢字のみで入力してください。';
            }
        }

        // カナ名チェック（カタカナのみ許可）
        if (!preg_match('/^[ァ-ヶー]+$/u', $kananame)) {
            $errs['kananame'] = '名前（カナ）はカタカナのみで入力してください。';
        }

        // 電話番号チェック
        if (!preg_match("/\A(\d{2,5})?\z/", $tel1) || // 市外局番の形式確認
            !preg_match("/\A(\d{1,4})?\z/", $tel2) || // 市内局番の形式確認
            !preg_match("/\A(\d{4})?\z/", $tel3)) {  // 加入者番号の形式確認
            $errs['tel'] = '電話番号は半角数字２～５桁、１～４桁、４桁で入力してください。';
        }

        // 入力にエラーがない場合の処理
        if (empty($errs)) {
            $kaiin = new Kaiin(); // 会員情報を格納するオブジェクトを作成
            $kaiin->kname      = $kname; // 漢字名を設定
            $kaiin->kananame   = $kananame; // カナ名を設定
            $kaiin->age        = $age; // 年齢を設定
            $kaiin->seibetu    = $seibetu; // 性別を設定
            $kaiin->email      = $email; // メールアドレスを設定
            $kaiin->oldpass    = $password; // パスワードを設定

            // 電話番号を連結して格納
            $kaiin->tel = '';
            if ($tel1 !== '' && $tel2 !== '' && $tel3 !== '') {
                $kaiin->tel = "{$tel1}-{$tel2}-{$tel3}"; // 電話番号を「-」で結合
            }

            // 会員情報をデータベースに登録
            $kaiinDAO->insert($kaiin);
            // 登録した会員情報を取得
            $Kard = $kaiinDAO->get_kaiin($email, $password);
            // 会員IDを使ってJECカードを登録
            $jeccardDAO->insert($Kard->kno);
            // 完了ページにリダイレクト
            header('Location:1_3signupEnd.php');
            exit; // スクリプトを終了
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8"> <!-- 文字コードをUTF-8に設定 -->
    <link href="css/LoginStyle.css" rel="stylesheet"> <!-- 外部CSSをリンク -->
    <title>新規登録</title> <!-- ページタイトル -->
</head>
<body>
    <?php include 'header.php'; ?> <!-- 共通ヘッダーを読み込み -->
    <h1>新規登録</h1> <!-- ページ見出し -->
    <p>以下の項目を入力し、登録ボタンをクリックしてください（*は必須）</p>
    <div class="center-wrapper">
        <div class="center">
            <form action="" method="POST"> <!-- 新規登録用フォーム -->
                <table>
                    <!-- 名前（漢字）入力 -->
                    <tr>
                        <td>名前（漢字）</td>
                        <td>
                            <input type="text" name="kname" placeholder="例）日電太郎" value="<?= htmlspecialchars($kname ?? '') ?>">
                        </td>
                    </tr>
                    <!-- 名前（カナ）入力 -->
                    <tr>
                        <td>名前（カナ）*</td>
                        <td>
                            <input type="text" name="kananame" required placeholder="例）ニチデンタロウ" value="<?= htmlspecialchars($kananame ?? '') ?>">
                        </td>
                    </tr>
                    <!-- 年齢 -->
                    <tr>
                        <td>年齢*</td>
                        <td>
                            <input type="number" name="age" required placeholder="例）20" value="<?= htmlspecialchars($age ?? '') ?>" min="1">
                        </td>
                    </tr>
                    <!-- 性別 -->
                    <tr>
                        <td>性別*</td>
                        <td>
                            <input type="radio" name="seibetu" value="男" <?= isset($seibetu) && $seibetu == "男" ? "checked" : "" ?>>男
                            <input type="radio" name="seibetu" value="女" <?= isset($seibetu) && $seibetu == "女" ? "checked" : "" ?>>女
                            <input type="radio" name="seibetu" value="×" <?= isset($seibetu) && $seibetu == "×" ? "checked" : "" ?>>回答なし
                        </td>
                    </tr>
                    <!-- メールアドレス -->
                    <tr>
                        <td>メールアドレス*</td>
                        <td>
                            <input type="email" name="email" required placeholder="例）sample@jec.ac.jp" value="<?= htmlspecialchars($email ?? '') ?>">
                        </td>
                    </tr>
                    <!-- メールアドレス再入力 -->
                    <tr>
                        <td>メールアドレス(再入力)*</td>
                        <td>
                            <input type="email" required name="email2" value="<?= htmlspecialchars($email2 ?? '') ?>">
                        </td>
                    </tr>
                    <!-- パスワード -->
                    <tr>
                        <td>パスワード(4文字以上15まで)*</td>
                        <td>
                            <input type="password" minlength="4" maxlength="15" required name="pass" value="<?= htmlspecialchars($password ?? '') ?>">
                        </td>
                    </tr>
                    <!-- パスワード再入力 -->
                    <tr>
                        <td>パスワード(再入力)*</td>
                        <td>
                            <input type="password" required name="pass2" value="<?= htmlspecialchars($password2 ?? '') ?>">
                        </td>
                    </tr>
                    <!-- 電話番号 -->
                    <tr>
                        <td>電話番号*</td>
                        <td>
                            <input type="tel" name="tel1" size="4" required placeholder="例）000" value="<?= htmlspecialchars($tel1 ?? '') ?>"> ‐ 
                            <input type="tel" name="tel2" size="4" required placeholder="0000" value="<?= htmlspecialchars($tel2 ?? '') ?>"> ‐ 
                            <input type="tel" name="tel3" size="4" required placeholder="0000" value="<?= htmlspecialchars($tel3 ?? '') ?>">
                        </td>
                    </tr>
                    <!-- エラーメッセージ表示 -->
                    <tr>
                        <td colspan="2">
                            <?php foreach($errs as $e) : ?>
                                <span style="color:red"><?= $e ?></span>
                                <br>
                            <?php endforeach; ?>
                        </td>
                    </tr>
                    <!-- 登録ボタン -->
                    <tr class="btn-container"> 
                        <td colspan="2"> 
                            <input type="submit" value="登録する" name="touroku" class="btn">
                        </td> 
                    </tr> 
                </table>
            </form>
        </div>
    </div>
</body>
</html>
