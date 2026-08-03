<?php

$root = dirname(__DIR__);
$iconsDir = $root.'/public/icons';

if (! extension_loaded('gd')) {
    fwrite(STDERR, "GD extension is required to generate PWA icons.\n");
    exit(1);
}

if (! is_dir($iconsDir)) {
    mkdir($iconsDir, 0755, true);
}

$sizes = [
    'icon-180.png' => 180,
    'icon-192.png' => 192,
    'icon-512.png' => 512,
];

foreach ($sizes as $filename => $size) {
    writeBrandIcon("{$iconsDir}/{$filename}", $size, false);
    echo "Wrote {$filename}\n";
}

writeBrandIcon("{$iconsDir}/icon-512-maskable.png", 512, true);
echo "Wrote icon-512-maskable.png\n";

writeBrandIcon("{$root}/public/kinsenas-square-logo.png", 512, false);
echo "Wrote kinsenas-square-logo.png\n";

function writeBrandIcon(string $path, int $size, bool $maskable): void
{
    $image = imagecreatetruecolor($size, $size);
    imagesavealpha($image, true);

    $teal = imagecolorallocate($image, 13, 115, 119);
    imagefill($image, 0, 0, $teal);

    $white = imagecolorallocate($image, 255, 255, 255);
    $padding = (int) round($size * ($maskable ? 0.19 : 0.16));
    $innerWidth = $size - ($padding * 2);
    $innerHeight = (int) round($innerWidth * 0.72);
    $top = (int) round(($size - $innerHeight) / 2);

    imagefilledrectangle(
        $image,
        $padding,
        $top,
        $padding + (int) round($innerWidth * 0.22),
        $top + $innerHeight,
        $white,
    );

    imagefilledrectangle(
        $image,
        $padding + (int) round($innerWidth * 0.28),
        $top,
        $padding + (int) round($innerWidth * 0.48),
        $top + (int) round($innerHeight * 0.42),
        $white,
    );

    imagefilledrectangle(
        $image,
        $padding + (int) round($innerWidth * 0.28),
        $top + (int) round($innerHeight * 0.48),
        $padding + (int) round($innerWidth * 0.72),
        $top + (int) round($innerHeight * 0.58),
        $white,
    );

    imagefilledrectangle(
        $image,
        $padding + (int) round($innerWidth * 0.52),
        $top + (int) round($innerHeight * 0.58),
        $padding + (int) round($innerWidth * 0.72),
        $top + $innerHeight,
        $white,
    );

    imagepng($image, $path);
    imagedestroy($image);
}
