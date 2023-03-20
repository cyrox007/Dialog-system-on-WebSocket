<?php

use Workerman\Worker;

require_once __DIR__ . '/vendor/autoload.php';

// Create a Websocket server
$ws_worker = new Worker('websocket://0.0.0.0:2346');

function conn_db() {
    return new SQLite3("test.db");
}

function get_msg($db, $user_id) {
    $sql = "SELECT utd.id, d.user_id, 
                    utd.dialog_id
                FROM user_to_dialog AS utd
                INNER JOIN dialoges AS d
                    ON utd.dialog_id = d.id
                WHERE utd.user_id = {$user_id}";
    $raw = $db->query($sql);
    
    $result = [];
    while ($row = $raw->fetchArray()) {
        $result[] = $row;
    }
    $msgArray = array();
    foreach ($result as $dbrow) {
        $d_id = $dbrow["dialog_id"];
        
        $sql2 = "SELECT * FROM messages WHERE dialog_id = '{$d_id}'";
        $raw2 = $db->query($sql2);

        while($row2 = $raw2->fetchArray()) {
            $messages[] = $row2;
        }
        $msgArray[$d_id] = $messages;
    }
    return $msgArray;
}

// Emitted when new connection come
$ws_worker->onConnect = function ($connection) use($connections) {
    $connection->onWebSocketConnect = function($connection) use($connections) {
        $user_id = $_GET["user_id"];
        $db = conn_db();
        
        $msgArray = get_msg($db, $user_id);

        $connection->id = $user_id;
        $connection->dialoges = $msgArray;
        $connection->pingWithoutResponseCount = 0;

        $connections[$connection->id] = $connection;

        $users = [];
        foreach ($connections as $c) {
            $users[] = [
                "userID" => $c->id,
                "userDialoges" => $c->dialoges,
            ];
        }

        $messageData = [
            'action' => 'Authorized',
            'userId' => $connection->id,
            'userDialoges' => $connection->dialoges,
        ];
        $connection->send(json_encode($messageData));
        $db->close();
    };
};

// Emitted when data received
$ws_worker->onMessage = function ($connection, $data) use (&$connections) {
    $msgData = json_decode($data, true);
    $db = conn_db();

    if ($msgData["action"] == "Message") {
        $text = $msgData["text"];
        $sender = $connection->id;
        $dialog = $msgData["dialogID"];
        $to = $msgData["to"];

        $sql = "INSERT INTO messages (dialog_id, user_id, text) VALUES ({$dialog}, {$sender}, '{$text}')";
        
        $db->query($sql);
        
        $newMsgArray = get_msg($db, $connection->id);

        $messageData = [
            'action'=> "Mess",
            'userDialoges' => $newMsgArray
        ];
        $connection->send(json_encode($messageData));
        if ($connections[$to] != null) {
            $connections[$to]->send(json_encode($messageData));
        }
        
    }
    $db->close();
};

// Emitted when connection closed
$ws_worker->onClose = function ($connection) {
    echo "Connection closed\n";
};

// Run worker
Worker::runAll();