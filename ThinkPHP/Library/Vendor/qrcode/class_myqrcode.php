<?php
/**
 * @author flybug
 * @version 1.0.0
 *
 * 二维码模块
 *
 * $errorCorrectionLevel     纠错级别：L、M、Q、H
 * $matrixPointSize          点的大小：图片每个黑点的像素
 *
 *
 */
require_once(WEBROOT . '/frame/lib/phpqrcode/phpqrcode.php');

class MyQRCode
{
    //直接输出到网页
    static public function getOutHtml($data, $errorCorrectionLevel = 'L', $matrixPointSize = 4)
    {
        QRcode::png($data, false, $errorCorrectionLevel, $matrixPointSize);
    }

    //输出为图片
    static public function getOutPic($data, $filename, $errorCorrectionLevel = 'L', $matrixPointSize = 4)
    {
        QRcode::png($data, $filename, $errorCorrectionLevel, $matrixPointSize, 2);
    }

    //输出为带Logo的二维码图片
    static public function getOutPicWithLogo($data, $filename, $logo, $errorCorrectionLevel = 'L', $matrixPointSize = 10)
    {
        QRcode::png($data, $filename, $errorCorrectionLevel, $matrixPointSize, 2);

        $QR = imagecreatefromstring(file_get_contents($filename));
        $logo = imagecreatefromstring(file_get_contents($logo));
        $QR_width = imagesx($QR);
        $QR_height = imagesy($QR);
        $logo_width = imagesx($logo);
        $logo_height = imagesy($logo);
        $logo_qr_width = $QR_width / 5;
        $scale = $logo_width / $logo_qr_width;
        $logo_qr_height = $logo_height / $scale;
        $from_width = ($QR_width - $logo_qr_width) / 2;
        imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width, $logo_qr_height, $logo_width, $logo_height);
        imagepng($QR, $filename);
    }

}
