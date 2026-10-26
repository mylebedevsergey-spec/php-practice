<?php
/**
 * Генератор CAPTCHA.
 * Рисует 5 случайных символов на шумовом фоне и сохраняет код в сессии.
 */

session_start();

$alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // без похожих символов (I/O/0/1)
$length = 5;
$code = '';
for ($i = 0; $i < $length; $i++) {
    $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
}
$_SESSION['captcha_code'] = $code;

$width = 240;
$height = 80;
$image = imagecreatetruecolor($width, $height);

// фон: шумовая картинка, если она есть, иначе градиент
$noisePath = __DIR__ . '/noise.jpg';
if (is_file($noisePath)) {
    $noise = imagecreatefromjpeg($noisePath);
    imagecopyresampled($image, $noise, 0, 0, 0, 0, $width, $height, imagesx($noise), imagesy($noise));
    imagedestroy($noise);
} else {
    for ($y = 0; $y < $height; $y++) {
        $shade = 235 - (int) ($y / 2);
        $color = imagecolorallocate($image, $shade, $shade, $shade);
        imageline($image, 0, $y, $width, $y, $color);
    }
}

// шумовые точки и линии
for ($i = 0; $i < 260; $i++) {
    $color = imagecolorallocate($image, random_int(185, 235), random_int(185, 235), random_int(185, 235));
    imagesetpixel($image, random_int(0, $width - 1), random_int(0, $height - 1), $color);
}
for ($i = 0; $i < 3; $i++) {
    $color = imagecolorallocate($image, random_int(150, 200), random_int(150, 200), random_int(150, 200));
    imageline($image, random_int(0, $width), random_int(0, $height), random_int(0, $width), random_int(0, $height), $color);
}

// символы: 5 штук с шагом 40 пт, размер 18–30, небольшой наклон
$font = __DIR__ . '/fonts/DejaVuSans-Bold.ttf';
for ($i = 0; $i < $length; $i++) {
    $size = random_int(18, 30);
    $angle = random_int(-18, 18);
    $x = 14 + $i * 40;
    $y = random_int(52, 66);
    $color = imagecolorallocate($image, random_int(10, 80), random_int(10, 80), random_int(10, 80));
    imagettftext($image, $size, $angle, $x, $y, $color, $font, $code[$i]);
}

header('Content-Type: image/png');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
imagepng($image);
imagedestroy($image);
