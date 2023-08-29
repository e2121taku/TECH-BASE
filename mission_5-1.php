<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8"/>
    <title>みんなの好きな映画</title>
</head>
<body>

<?php
$servername = "localhost"; // データベースサーバー名
$username = "ユーザー名"; // ユーザー名
$password = "パスワード"; // パスワード
$dbname = "データベース名"; // データベース名

// データベースに接続
try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "データベース接続エラー: " . $e->getMessage();
}

// 新しい投稿のIDを計算
$sql = "SELECT MAX(id) AS max_id FROM tbtest";
$stmt = $pdo->query($sql);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$newID = ($row['max_id'] ? $row['max_id'] : 0) + 1;

if ($_SERVER["REQUEST_METHOD"] === "POST"){
    $name = $_POST["name"];
    $comment = $_POST["comment"];
    $password = $_POST["password"];
    $day = date("Y/m/d H:i:s");

    if(isset($_POST["comment"]) && !empty($_POST["comment"])) {
        if (empty($_POST["editnumber"])) {
            // 新規投稿
            $sql = "INSERT INTO tbtest (id, name, comment, password) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$newID, $name, $comment, $password]);
        } elseif (isset($_POST["editnumber"]) && !empty($_POST["editnumber"])) {
            $editnum = intval($_POST["editnumber"]);
            // 編集
            $sql = "UPDATE tbtest SET name=?, comment=?, password=? WHERE id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$name, $comment, $password, $editnum]);
        }
    }

    if (isset($_POST["delete"]) && !empty($_POST["delete"])) {
        $delnum = intval($_POST["delete"]);
        $delete_password = $_POST["delete_password"]; // 削除用パスワードを取得

        // 削除対象のデータを取得
        $sql = "SELECT * FROM tbtest WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$delnum]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && $delete_password == $row['password']) {
            // パスワードが一致する場合にのみ削除
            $sql = "DELETE FROM tbtest WHERE id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$delnum]);
            echo "投稿番号 " . $delnum . " が削除されました。";
        } else {
            echo "削除に失敗しました。投稿番号またはパスワードが正しくありません。";
        }
    }

    if (isset($_POST["edit"]) && !empty($_POST["edit"])) {
        $editnum = intval($_POST["edit"]);
        // 編集対象のデータを取得
        $sql = "SELECT * FROM tbtest WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$editnum]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $enum = $row["id"];
            $ename = $row["name"];
            $ecomment = $row["comment"];
        }
    }
}
?>

<form action="" method="post">
    <input type="text" name="name" placeholder="名前" value="<?php if(isset($ename)) {echo $ename;} ?>"><br>
    <input type="text" name="comment" placeholder="コメント" value="<?php if(isset($ecomment)) {echo $ecomment;} ?>">
    <input type="hidden" name="editnumber" value="<?php if(isset($enum)) {echo $enum;} ?>">
    <input type="text" name="password" placeholder="パスワード">
    <input type="submit" value="送信">
   
   <br><br>
<input type="number" name="delete" placeholder="削除対象番号">
<input type="password" name="delete_password" placeholder="削除パスワード">
<input type="submit" value="削除">
<br><br>
<input type="number" name="edit" placeholder="編集対象番号">
<input type="password" name="edit_password" placeholder="編集パスワード">
<input type="submit" value="編集">
</form>

<br>

<?php
// データベースからデータを取得して表示
$sql = "SELECT * FROM tbtest";
$stmt = $pdo->query($sql);
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $postNum = $row["id"];
    $postName = $row["name"];
    $postComment = $row["comment"];
    $postPassword = $row["password"]; // パスワードを取得
    echo $postNum . " " . $postName . " " . $postComment . " パスワード: " . $postPassword . "<br>";
}
?>

</body>
</html>
