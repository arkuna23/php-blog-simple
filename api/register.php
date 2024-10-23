<?php

include_once "lib.php";

session_start();

function check_username($username)
{
    if (strlen($username) < 5 || strlen($username) > 20) {
        writeLog("[user: $username]Username length error", LogLevel::ERROR);
        json_err("用户名长度错误", 400);
    }
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        writeLog("[user: $username]Username format error", LogLevel::ERROR);
        json_err("用户名格式错误", 400);
    }
}

json_service([
    new JsonServ('POST', function ($json) {
        $input_username = $json['username'];
        $password = $json['password'];
        $captcha = $json['captcha'];

        check_captcha($captcha, $input_username);
        check_username($input_username);
        check_password($password, $input_username);

        $conn = mysql_conn();
        if ($conn->connect_error) {
            writeLog(
                "[user: $input_username]DB Connection failed: " . $conn->connect_error,
                LogLevel::ERROR
            );
            json_err("连接失败: " . $conn->connect_error, 500);
        }

        $sql = "SELECT * FROM tb_users WHERE username = ?;";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $input_username);
        $stmt->execute();

        if ($stmt->get_result()->num_rows > 0) {
            writeLog("[user: $input_username]Username exists", LogLevel::ERROR);
            json_err("用户名已存在", 409);
        } else {
            $hashed_password = hash('sha256', $password);
            $sql = "INSERT INTO users (username, password) VALUES (?, ?);";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $input_username, $hashed_password);
            $stmt->execute();
            writeLog("[user: $input_username]Register successful");
            setcookie('session', encrypt($input_username, SESSION_KEY), time() + (86400 * 30), "/", "", true, true);
            echo json_data(true, "", null);
        }
    })
]);
