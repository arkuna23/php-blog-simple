<?php

include_once "lib.php";

session_start();
json_service([
    new JsonServ('POST', function ($json) {
        $input_username = $json['username'];
        $password = $json['password'];
        $new_password = $json['new_password'];
        $captcha = $json['captcha'];

        check_captcha($captcha, $input_username);

        $conn = mysql_conn();
        $sql = "SELECT password FROM tb_users WHERE username = ?;";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $input_username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            writeLog("[user: $input_username]User not found", LogLevel::ERROR);
            json_err("用户不存在", 404);
        } else {
            $row = $result->fetch_assoc();
            $hashed_password = hash('sha256', $password);
            if ($row['password'] !== $hashed_password) {
                writeLog("[user: $input_username]Password mismatch", LogLevel::ERROR);
                json_err("密码错误", 403);
            }
        }

        check_password($new_password, $input_username);
        $sql = "UPDATE tb_users SET password = ? WHERE username = ?;";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", hash('sha256', $new_password), $input_username);
        $stmt->execute();
        writeLog("[user: $input_username]Password modified");
        echo json_data(true, "", null);
    })
]);
