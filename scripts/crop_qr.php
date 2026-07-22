<?php

$srcPath = __DIR__.'/../public/images/qr-egide.jpeg';
$src = @imagecreatefromjpeg($srcPath);

if (! $src) {
    fwrite(STDERR, "Failed to load {$srcPath}\n");
    exit(1);
}

$w = imagesx($src);
$h = imagesy($src);
$minX = $w;
$minY = $h;
$maxX = 0;
$maxY = 0;

for ($y = 0; $y < $h; $y++) {
    for ($x = 0; $x < $w; $x++) {
        $rgb = imagecolorat($src, $x, $y);
        $r = ($rgb >> 16) & 0xFF;
        $g = ($rgb >> 8) & 0xFF;
        $b = $rgb & 0xFF;

        if ($r < 245 || $g < 245 || $b < 245) {
            $minX = min($minX, $x);
            $minY = min($minY, $y);
            $maxX = max($maxX, $x);
            $maxY = max($maxY, $y);
        }
    }
}

$pad = 20;
$minX = max(0, $minX - $pad);
$minY = max(0, $minY - $pad);
$maxX = min($w - 1, $maxX + $pad);
$maxY = min($h - 1, $maxY + $pad);
$cw = $maxX - $minX + 1;
$ch = $maxY - $minY + 1;

$crop = imagecrop($src, [
    'x' => $minX,
    'y' => $minY,
    'width' => $cw,
    'height' => $ch,
]);

$scale = 2;
$tw = $cw * $scale;
$th = $ch * $scale;
$out = imagecreatetruecolor($tw, $th);
$white = imagecolorallocate($out, 255, 255, 255);
imagefill($out, 0, 0, $white);
imagecopyresampled($out, $crop, 0, 0, 0, 0, $tw, $th, $cw, $ch);

$pngPath = __DIR__.'/../public/images/qr-egide.png';
$jpgPath = __DIR__.'/../public/images/qr-egide.jpeg';
imagepng($out, $pngPath, 1);
imagejpeg($out, $jpgPath, 95);

echo "source={$w}x{$h}\n";
echo "cropped={$cw}x{$ch} -> {$tw}x{$th}\n";
echo "png=".filesize($pngPath)." jpeg=".filesize($jpgPath)."\n";
