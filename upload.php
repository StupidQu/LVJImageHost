<?php

function compressImage($sourceImage, $quality = 80)
{
    $width = imagesx($sourceImage);
    $height = imagesy($sourceImage);
    $compressedImage = imagecreatetruecolor($width, $height);
    imagecopyresampled($compressedImage, $sourceImage, 0, 0, 0, 0, $width, $height, $width, $height);
    return $compressedImage;
}

function imageSize($image)
{
    ob_start();
    imagepng($image);
    $size = strlen(ob_get_contents());
    ob_end_clean();
    return $size;
}

$base_url = 'https://img.zshfoj.com';
$response_data = array('success' => true, 'url' => '', 'msg' => '');

if (!isset($_POST['image'])) {
    $response_data['success'] = false;
    $response_data['msg'] = '[Image] required.';
} else {
    $binary = imagecreatefromstring(base64_decode($_POST['image']));
    $quality = 100;
    while (imageSize($binary) > 512 * 1024 && $quality > 20) {
        $quality = $quality - 80;
        $binary = compressImage($binary, $quality);
    }
    if ($quality <= 20) {
        $response_data['success'] = false;
        $response_data['msg'] = 'Image larger than 512KB cannot be upload.';
    } else {
        ob_start();
        imagepng($binary);
        $sha256 = hash('sha256', ob_get_contents());
        ob_end_clean();
        $link = $base_url . '/' . $sha256 . '.png';
        if (file_exists($sha256 . '.png')) {
            $response_data['url'] = $link;
        } else {
            $file = fopen($sha256 . '.png', 'w');
            imagepng($binary, $file);
            $response_data['url'] = $link;
        }
    }
    echo json_encode($response_data);
}
?>