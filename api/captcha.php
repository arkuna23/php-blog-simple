<?php

session_start();

$width = 100;
$height = 40;

$captcha_code = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 6);
$_SESSION['captcha'] = $captcha_code;

$image = imagecreatetruecolor($width, $height);
$background_color = imagecolorallocate($image, 255, 255, 255);
$text_color = imagecolorallocate($image, 0, 0, 0);
$line_color = imagecolorallocate($image, 64, 64, 64);

imagefilledrectangle($image, 0, 0, $width, $height, $background_color);

for ($i = 0; $i < 5; $i++) {
    imageline($image, rand(0, $width), rand(0, $height), rand(0, $width), rand(0, $height), $line_color);
}

$font_size = 5;

$text_width = imagefontwidth($font_size) * strlen($captcha_code);
$text_height = imagefontheight($font_size);

$x = ($width - $text_width) / 2;
$y = ($height - $text_height) / 2;

imagestring($image, $font_size, $x, $y, $captcha_code, $text_color);

header('Content-Type: image/png');
imagepng($image);
imagedestroy($image);
