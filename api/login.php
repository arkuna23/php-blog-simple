<?php

include_once "lib.php";

session_start();

json_service([
    new JsonServ('POST', function ($json) {
        $input_username = $json['username'] ?? '';
        $input_password = $json['password'] ?? '';
        $input_captcha = $json['captcha'] ?? '';
        if (strcasecmp($input_captcha, $_SESSION['captcha']) !== 0) {
            writeLog("[user: $input_username]Captcha mismatch", LogLevel::ERROR);
            json_err("验证码错误", 400);
        }
        $hashed_password = hash('sha256', $input_password);

        $conn = mysql_conn();
        if ($conn->connect_error) {
            writeLog("[user: $input_username]Database connection error: " . $conn->connect_error, LogLevel::ERROR);
            json_err("数据库连接错误: " . $conn->connect_error, 500);
        }
        $sql = "SELECT * FROM users WHERE username = ? AND password = ?;";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $input_username, $hashed_password);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            writeLog("[user: $input_username]Login successful");
            setcookie('session', encrypt($input_username, SESSION_KEY), time() + (86400 * 30), "/", "", true, true);
            echo json_data(true, "", null);
        } else {
            writeLog("[user: $input_username]Login failed", LogLevel::ERROR);
            json_err("用户名或密码错误", 400, false);
        }

        $stmt->close();
        $conn->close();

        unset($_SESSION['captcha']);
    })
]);
