<?php

include_once "lib.php";

session_start();
$username = get_username();

$post_serv = new JsonServ('POST', function () use ($username) {
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    if (isset($_FILES['avatar'])) {
        $file = $_FILES['avatar'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            writeLog("上传文件失败: " . $file['error'], );
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

        $fileName = 'avatar_' . $username . '.' . $fileExtension;
        $uploadFilePath = $uploadDir . $fileName;

        if (move_uploaded_file($file['tmp_name'], $uploadFilePath)) {
            json_data(true, "头像上传成功");
        } else {
            json_err("文件上传失败", 500);
        }
    }
});
$get_serv = new JsonServ('GET', function () use ($username) {
    $avatarDir = '../uploads/';
    $avatarPath = $avatarDir . 'avatar_' . $username . '.jpg';
    if (file_exists($avatarPath)) {
        header('Content-Type: image/jpeg');
        readfile($avatarPath);
    } else {
        header('Content-Type: image/png');
        readfile('../assets/default_avatar.jpg');
    }
});

json_service([$post_serv, $get_serv]);
