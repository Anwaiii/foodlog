<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;

class ImageOptimizer
{
    /**
     * 画像を最大サイズへ自動リサイズ＆圧縮して保存する。
     * PHP GD が利用できない環境やアニメGIF等の場合はそのまま安全に移動する。
     */
    public static function saveOptimized(
        UploadedFile $file,
        string $targetDir,
        string $publicPrefix,
        string $filenamePrefix,
        int $maxDimension = 1200,
        bool $squareCrop = false,
        int $jpegQuality = 82
    ): string {
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
            $ext = 'jpg';
        }

        $baseName = $filenamePrefix . '_' . time() . '_' . rand(1000, 9999);
        $sourcePath = $file->getRealPath();

        // GD が使えない、または GIF（アニメーション保持）の場合はそのまま保存
        if (!extension_loaded('gd') || !$sourcePath || $ext === 'gif') {
            $filename = $baseName . '.' . $ext;
            $file->move($targetDir, $filename);
            return rtrim($publicPrefix, '/') . '/' . $filename;
        }

        $info = @getimagesize($sourcePath);
        if (!$info || empty($info[0]) || empty($info[1])) {
            $filename = $baseName . '.' . $ext;
            $file->move($targetDir, $filename);
            return rtrim($publicPrefix, '/') . '/' . $filename;
        }

        $mime = $info['mime'] ?? '';
        $src = match ($mime) {
            'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($sourcePath),
            'image/png'  => @imagecreatefrompng($sourcePath),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($sourcePath) : false,
            default      => false,
        };

        if (!$src) {
            $filename = $baseName . '.' . $ext;
            $file->move($targetDir, $filename);
            return rtrim($publicPrefix, '/') . '/' . $filename;
        }

        // スマホ写真の EXIF 回転補正（JPEG）
        if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {
            $exif = @exif_read_data($sourcePath);
            $orientation = (int) ($exif['Orientation'] ?? 1);
            if ($orientation === 3) {
                $src = imagerotate($src, 180, 0) ?: $src;
            } elseif ($orientation === 6) {
                $src = imagerotate($src, -90, 0) ?: $src;
            } elseif ($orientation === 8) {
                $src = imagerotate($src, 90, 0) ?: $src;
            }
        }

        $srcW = imagesx($src);
        $srcH = imagesy($src);

        if ($squareCrop) {
            $side = min($srcW, $srcH);
            $srcX = (int) floor(($srcW - $side) / 2);
            $srcY = (int) floor(($srcH - $side) / 2);
            $dstW = min($side, $maxDimension);
            $dstH = $dstW;
            $copyW = $side;
            $copyH = $side;
        } else {
            $srcX = 0;
            $srcY = 0;
            $copyW = $srcW;
            $copyH = $srcH;
            $scale = min(1.0, $maxDimension / max($srcW, $srcH));
            $dstW = max(1, (int) round($srcW * $scale));
            $dstH = max(1, (int) round($srcH * $scale));
        }

        $dst = imagecreatetruecolor($dstW, $dstH);
        $isPng = ($mime === 'image/png');

        if ($isPng) {
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
            $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
            imagefilledrectangle($dst, 0, 0, $dstW, $dstH, $transparent);
        }

        imagecopyresampled($dst, $src, 0, 0, $srcX, $srcY, $dstW, $dstH, $copyW, $copyH);

        $outExt = $isPng ? 'png' : 'jpg';
        $filename = $baseName . '.' . $outExt;
        $fullDest = rtrim($targetDir, DIRECTORY_SEPARATOR . '/') . DIRECTORY_SEPARATOR . $filename;

        $saved = $isPng
            ? @imagepng($dst, $fullDest, 7)
            : @imagejpeg($dst, $fullDest, $jpegQuality);

        imagedestroy($src);
        imagedestroy($dst);

        if (!$saved) {
            $fallbackName = $baseName . '.' . $ext;
            $file->move($targetDir, $fallbackName);
            return rtrim($publicPrefix, '/') . '/' . $fallbackName;
        }

        return rtrim($publicPrefix, '/') . '/' . $filename;
    }
}
