<?php

class ImageService
{

    public static function uploadAndGenerateImages(
        array $file,
        string $uploadDir = __DIR__ . '/../../uploads/',
        array $sizes = [
            'cover' => [800, 450],
            'thumb' => [200, 130],
            'mini'  => [70, 45]
        ],
        int $quality = 80
    ): ?string {

        if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $info = getimagesize($file['tmp_name']);
        if ($info === false) return null;

        $mime = $info['mime'];

        switch ($mime) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($file['tmp_name']);
                break;
            case 'image/png':
                $image = imagecreatefrompng($file['tmp_name']);
                break;
            case 'image/webp':
                $image = imagecreatefromwebp($file['tmp_name']);
                break;
            case 'image/gif':
                $image = imagecreatefromgif($file['tmp_name']);
                break;
            default:
                return null;
        }

        $width = imagesx($image);
        $height = imagesy($image);

        $baseName = uniqid('img_');
        $index = 0;
        foreach ($sizes as $key => [$targetWidth, $targetHeight]) {

            $newImage = imagecreatetruecolor($targetWidth, $targetHeight);

            $srcRatio = $width / $height;
            $targetRatio = $targetWidth / $targetHeight;

            if ($srcRatio > $targetRatio) {
                $newHeightSrc = $height;
                $newWidthSrc = $height * $targetRatio;
                $srcX = ($width - $newWidthSrc) / 2;
                $srcY = 0;
            } else {
                $newWidthSrc = $width;
                $newHeightSrc = $width / $targetRatio;
                $srcX = 0;
                $srcY = ($height - $newHeightSrc) / 2;
            }

            imagecopyresampled(
                $newImage,
                $image,
                0,
                0,
                $srcX,
                $srcY,
                $targetWidth,
                $targetHeight,
                $newWidthSrc,
                $newHeightSrc
            );
            if ($index == 0)
                $fileName = $baseName . '.jpg';
            else
                $fileName = $baseName . '_' . $key . '.jpg';
            $filePath = $uploadDir . $fileName;

            imagejpeg($newImage, $filePath, $quality);
            $index++;
            $newImage = null;
        }

        $image = null;

        return $baseName . '.jpg';
    }
}
