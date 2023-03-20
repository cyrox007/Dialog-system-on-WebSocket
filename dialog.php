<?php
    include_once "main.php";
    $user_id = $_GET["user_id"];
    $m = new Main();
    $users = $m->get_dialog($user_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Пользователь <?=$user_id;?></title>
    <style>
        <? include_once "styles/style.css" ?>
    </style>
</head>
<body>
    <main>
        <aside>
            <? foreach ($users as $user):?>
                <div class="item" id="<? echo $user["user_id"] ?>" dialog-id="<? echo $user["dialog_id"];?>">Пользователь <? echo $user["user_id"] ?></div>    
            <? endforeach; ?>
        </aside>
        <section>
            <header></header>
            <div class="send_msgs" id="send_msgs"></div>
            <div class="send_field" id="dialog-send-field" dialog-id="">
                <input type="text" name="text" id="text" placeholder="msg">
                <button id="send">Send</button>
            </div>
        </section>
    </main>
    <script>
        <? include_once "scripts/script.js";?>
    </script>
</body>
</html>