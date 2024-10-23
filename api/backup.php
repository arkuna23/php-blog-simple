<?php

include_once "lib.php";

session_start();

if (get_username() !== 'admin') {
    json_err("只有管理员可以进行数据备份", 403);
}

function backup_users($conn)
{
    $data = [];
    $stmt = $conn->prepare("SELECT * FROM users");
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    $jsonFile = '../backup/users_' . date('Y-m-d_H-i-s') . '.json';
    file_put_contents($jsonFile, json_encode($data));
}

function backup_messages($conn)
{
    $data = [];
    $stmt = $conn->prepare("SELECT * FROM messages");
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    $jsonFile = '../backup/messages_' . date('Y-m-d_H-i-s') . '.json';
    file_put_contents($jsonFile, json_encode($data));
}

$get_serv = new JsonServ('GET', function ($params) {
    $conn = mysql_conn();
    switch ($params['target']) {
        case 'users':
            backup_users($conn);
            break;
        case 'messages':
            backup_messages($conn);
            break;
        default:
            backup_users($conn);
            backup_messages($conn);
    }

    json_data(true, "");
});

json_service([$get_serv]);
