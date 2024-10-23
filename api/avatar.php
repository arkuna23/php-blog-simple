<?php

require_once "lib.php";

session_start();
$username = get_username();


$post_serv = new JsonServ('POST', function () use ($username) {
    $uploadDir = '../uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    if (isset($_FILES['avatar'])) {
        $file = $_FILES['avatar'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $level = LogLevel::ERROR;
            writeLog("上传文件失败: " . $file['error'], $level);
            json_err("文件上传失败", 500);
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($file['type'], $allowedTypes)) {
            json_err("只允许上传 JPEG、PNG 或 GIF 格式的图片。", 400);
        }

        $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
        if (!in_array(strtolower($fileExtension), ['jpg', 'jpeg', 'png', 'gif'])) {
            json_err("只允许上传 JPEG、PNG 或 GIF 格式的图片。", 400);
        }

        foreach (['jpg', 'jpeg', 'png', 'gif'] as $type) {
            $fileName = 'avatar_' . $username . '.' . $type;
            $uploadFilePath = $uploadDir . $fileName;

            if (file_exists($uploadFilePath)) {
                if (!unlink($uploadFilePath)) {
                    writeLog("删除旧头像失败: $fileName", LogLevel::ERROR);
                    json_err("无法删除旧头像", 500);
                }
            }
        }

        $fileName = 'avatar_' . $username . '.' . $fileExtension;
        $uploadFilePath = $uploadDir . $fileName;

        if (move_uploaded_file($file['tmp_name'], $uploadFilePath)) {
            echo json_data(true, "头像上传成功");
        } else {
            writeLog("上传文件失败: $fileName", LogLevel::ERROR);
            json_err("文件上传失败", 500);
        }
    }
});
$get_serv = new JsonServ('GET', function ($params) use ($username) {
    $avatarDir = '../uploads/';
    if (isset($params['username'])) {
        $username = $params['username'];
    }
    $avatarPath = $avatarDir . 'avatar_' . $username;
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    $foundImage = false;

    foreach ($allowedExtensions as $extension) {
        if (file_exists($avatarPath . '.' . $extension)) {
            $avatarPath .= '.' . $extension;
            $foundImage = true;
            break;
        }
    }

    if ($foundImage) {
        $extension = pathinfo($avatarPath, PATHINFO_EXTENSION);
        header('Content-Type: image/' . $extension);
        readfile($avatarPath);
    } else {
        header('Content-Type: image/jpg');
        readfile('../assets/default_avatar.jpg');
    }
});

json_service([$post_serv, $get_serv]);
