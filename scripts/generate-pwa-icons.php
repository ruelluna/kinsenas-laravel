<?php

$root = dirname(__DIR__);
$sourcePath = $root.'/public/kinsenas-square-logo.png';
$iconsDir = $root.'/public/icons';

if (! extension_loaded('gd')) {
    fwrite(STDERR, "GD extension is required to generate PWA icons.\n");
    exit(1);
}

if (! is_file($sourcePath)) {
    fwrite(STDERR, "Source logo missing: public/kinsenas-square-logo.png\n");
    fwrite(STDERR, "Add the brand square PNG before running icons:pwa.\n");
    exit(1);
}

if (! is_dir($iconsDir)) {
    mkdir($iconsDir, 0755, true);
}

$source = loadSquareImage($sourcePath);

$sizes = [
    'icon-180.png' => 180,
    'icon-192.png' => 192,
    'icon-512.png' => 512,
];

foreach ($sizes as $filename => $size) {
    writeResizedIcon($source, "{$iconsDir}/{$filename}", $size, false);
    echo "Wrote {$filename}\n";
}

writeResizedIcon($source, "{$iconsDir}/icon-512-maskable.png", 512, true);
echo "Wrote icon-512-maskable.png\n";

imagedestroy($source);

/**
 * @return \GdImage
 */
function loadSquareImage(string $path)
{
    $image = imagecreatefrompng($path);

    if ($image === false) {
        fwrite(STDERR, "Could not read PNG: {$path}\n");
        exit(1);
    }

    imagesavealpha($image, true);

    return $image;
}

/**
 * @param  \GdImage  $source
 */
function writeResizedIcon($source, string $path, int $size, bool $maskable): void
{
    $target = imagecreatetruecolor($size, $size);
    imagesavealpha($target, true);

    if ($maskable) {
        $background = imagecolorallocate($target, 255, 255, 255);
        imagefill($target, 0, 0, $background);
        $innerSize = (int) round($size * 0.72);
        $offset = (int) round(($size - $innerSize) / 2);
    } else {
        $transparent = imagecolorallocatealpha($target, 0, 0, 0, 127);
        imagefill($target, 0, 0, $transparent);
        $innerSize = $size;
        $offset = 0;
    }

    $sourceWidth = imagesx($source);
    $sourceHeight = imagesy($source);

    imagecopyresampled(
        $target,
        $source,
        $offset,
        $offset,
        0,
        0,
        $innerSize,
        $innerSize,
        $sourceWidth,
        $sourceHeight,
    );

    imagepng($target, $path);
    imagedestroy($target);
}
