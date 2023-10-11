<?php 
    #DB接続設定
    $dsn = 'mysql:dbname=データベース名;host=localhost';
    $user = 'ユーザ名';
    $password = 'パスワード';
    $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
    
    
    #テーブルmission5を作成
    $sql = "CREATE TABLE IF NOT EXISTS mission5"
    ." ("
    . "id INT AUTO_INCREMENT PRIMARY KEY,"
    . "name CHAR(32),"
    . "comment TEXT,"
    . "date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,"
    . "pass INT(16)"
    .");";
    $stmt = $pdo->query($sql);
    
    #フォームの初期値設定
    $edit_num = "";
    $edit_name = "";
    $edit_comment = "";
    $edit_pass = "";
    
    #ここにはパスワード確認の関数(投稿番号、入力されたパスワード、DB接続)
    function password_match($post_num, $input_pass,$pdo){

        $id = $post_num;
        $sql = 'SELECT * FROM mission5 WHERE id=:id '; # SQL
        $stmt = $pdo->prepare($sql);                   # 差し替えるパラメータを含めて記述したSQLを準備し、
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);  # その差し替えるパラメータの値を指定してから、
        $stmt->execute();                              # SQLを実行する。
        $results = $stmt->fetchAll();     
        
        //$rowの中にはテーブルのカラム名が入る
        foreach($results as $row){
            if($row["pass"] == $input_pass){return 0;} #パスワードが一致なら０を返す
            else{return 1;} #パスワードが不一致なら１を返す
        }
    }
    
    #入力フォーム
    if(!empty($_POST["name"]) & !empty($_POST["comment"]) &!empty($_POST["pass"])){
        #新規登録
        if(empty($_POST["post_num"])){
            $name = $_POST["name"];
            $comment = $_POST["comment"]; 
            $pass = $_POST["pass"];
            
            $sql = "INSERT INTO mission5 (name, comment, pass) VALUES (:name, :comment, :pass)";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
            $stmt->bindParam(':pass', $pass, PDO::PARAM_INT);
            $stmt->execute();
        }
        #編集
        else{
            $id = $_POST["post_num"]; #変更する投稿番号
            $name = $_POST["name"];
            $comment = $_POST["comment"];
            $pass = $_POST["pass"];
            
            $sql = 'UPDATE mission5 SET name=:name,comment=:comment,pass=:pass WHERE id=:id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
            $stmt->bindParam(':pass', $pass, PDO::PARAM_INT);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
        }
    }
    #削除フォーム
    if(!empty($_POST["remove_num"])){
        if(password_match($_POST["remove_num"],$_POST["remove_pass"],$pdo) == 0){
            $id = $_POST["remove_num"];
            $sql = 'delete from mission5 where id=:id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
        }
    }
    #編集フォーム
    if(!empty($_POST["edit_num"])){
        if(password_match($_POST["edit_num"],$_POST["edit_pass"],$pdo) == 0){
            $edit_good = "good"; #フォームのボタンが編集という表示変える
            $id = $_POST["edit_num"];
            
            $sql = 'SELECT * FROM mission5 WHERE id=:id'; #SQL
            $stmt = $pdo->prepare($sql);                  #差し替えるパラメータを含めて記述したSQLを準備し、
            $stmt->bindParam(':id', $id, PDO::PARAM_INT); #その差し替えるパラメータの値を指定してから、
            $stmt->execute();                             #SQLを実行する。
            $results = $stmt->fetchAll();   
            //$rowの中にはテーブルのカラム名が入る
            foreach($results as $row){
                $edit_num = $row['id'];
                $edit_name = $row['name'];
                $edit_comment = $row['comment'];
                $edit_pass = $row['pass'];
            }
        }
    }  
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>mission5</title>
</head>
<body>
  <p>あなたの名前、コメント、パスワード(数値１６桁以下)を入力してください</p>
  <form action="m5-1.php" method="POST">
    <input type="hidden" name="post_num" value="<?= $edit_num ?>">
    <input type="text" name="name" placeholder="名前" value="<?= $edit_name ?>"><br>
    <input type="text" name="comment" placeholder="コメント" value="<?= $edit_comment ?>"><br>
    <input type="password" name="pass" placeholder="パスワード" value="<?= $edit_pass ?>">
    <?php if(empty($edit_good)){ $value="送信";} else{ $value="編集";} ?>
    <input type="submit" value=<?= $value ?>>
    <p>削除したい投稿番号とパスワードを入力してください</p>
    <input type="number" name="remove_num" placeholder="投稿番号"><br>
    <input type="password" name="remove_pass" placeholder="パスワード">
    <input type="submit" value="削除"><br>
    <p>編集したい投稿番号とパスワードを入力してください</p>
    <input type="number" name="edit_num" placeholder="投稿番号"><br>
    <input type="password" name="edit_pass" placeholder="パスワード">
    <input type="submit" value="選択"><br><br>
  </form>
  <?php
    #一覧表示
    $sql = 'SELECT * FROM mission5';
    $stmt = $pdo->query($sql);
    $results = $stmt->fetchAll();
    foreach ($results as $row){
        //$rowの中にはテーブルのカラム名が入る
        echo "投稿番号:".$row["id"]." 名前:".$row["name"]." コメント:".$row["comment"]." 投稿日時:".$row["date"];
    echo "<hr>";
    }
  ?>
</body>
</html>