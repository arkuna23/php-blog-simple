<?php

include_once "lib.php";

session_start();

$username = get_username();
$conn = mysql_conn();
if ($conn->connect_error) {
    writeLog("数据库连接错误: " . $conn->connect_error, LogLevel::ERROR);
    json_err("数据库连接错误: " . $conn->connect_error, 500);
}

$add_serv = new JsonServ(
    'POST',
    function ($json) use ($username, $conn) {
        $sql = "INSERT INTO tb_messages (username, message) VALUES (?, ?);";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $json['message']);
        $stmt->execute();

        echo json_data(true, "留言成功");
    }
);
$delete_serv = new JsonServ(
    'DELETE',
    function ($params) use ($username, $conn) {
        $sql = "DELETE FROM tb_messages WHERE username = ? AND id = ?;";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $username, $params['id']);
        $stmt->execute();
        if ($stmt->affected_rows == 0) {
            echo json_err("删除失败", 500);
        } else {
            echo json_data(true, "删除成功");
        }
    }
);
$get_serv = new JsonServ(
    'GET',
    function ($data) use ($conn) {
        if (isset($data['username'])) {
            $sql = "SELECT * FROM tb_messages WHERE username = ?;";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $data['username']);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $sql = "SELECT * FROM tb_messages;";
            $result = $conn->query($sql);
        }

        $messages = [];
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }
        echo json_data(true, "", $messages);
    }
);

json_service([
    $add_serv,
    $delete_serv,
    $get_serv,
]);
