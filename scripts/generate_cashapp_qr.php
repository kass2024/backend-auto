<?php

require __DIR__.'/../vendor/autoload.php';

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

$cashtag = '$EgideNiringiyimana';
$url = 'https://cash.app/'.$cashtag;
$out = __DIR__.'/../public/images/qr-cashapp.png';

$options = new QROptions([
    'outputType' => QRCode::OUTPUT_IMAGE_PNG,
    'eccLevel' => QRCode::ECC_M,
    'scale' => 12,
    'imageBase64' => false,
    'outputFile' => $out,
]);

(new QRCode($options))->render($url, $out);

if (! is_file($out) || filesize($out) < 500) {
    fwrite(STDERR, "QR generation failed for {$url}\n");
    exit(1);
}

$size = getimagesize($out);
echo "OK {$out}\n";
echo 'bytes='.filesize($out)."\n";
echo 'dims='.$size[0].'x'.$size[1]."\n";
echo "data={$url}\n";
