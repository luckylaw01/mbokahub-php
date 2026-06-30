<?php
/**
 * Image Processing Helper - MbokaHub
 * Handles image compression and conversion to WebP format using GD Library.
 */

/**
 * Compress an image and convert it to WebP format.
 *
 * @param string $sourcePath Path to the uploaded temporary file.
 * @param string $destinationPath Path where the compressed WebP image should be saved.
 * @param int $quality Compression quality (0-100). Default is 80.
 * @return bool True on success, false on failure.
 */
function compressAndConvertToWebp($sourcePath, $destinationPath, $quality = 80) {
    // Check if the GD extension's WebP functions are available
    if (!function_exists('imagewebp')) {
        return false;
    }

    // Get image dimensions and MIME type
    $info = @getimagesize($sourcePath);
    if ($info === false) {
        return false;
    }

    $mime = $info['mime'];
    switch ($mime) {
        case 'image/jpeg':
        case 'image/jpg':
            $image = @imagecreatefromjpeg($sourcePath);
            break;
        case 'image/png':
            $image = @imagecreatefrompng($sourcePath);
            if ($image) {
                // Keep transparency for PNG conversion
                imagepalettetotruecolor($image);
                imagealphablending($image, false);
                imagesavealpha($image, true);
            }
            break;
        case 'image/gif':
            $image = @imagecreatefromgif($sourcePath);
            if ($image) {
                imagepalettetotruecolor($image);
            }
            break;
        case 'image/webp':
            $image = @imagecreatefromwebp($sourcePath);
            break;
        default:
            return false;
    }

    // If image resource creation failed, return false
    if (!$image) {
        return false;
    }

    // Save as WebP format at the specified quality
    $result = @imagewebp($image, $destinationPath, $quality);

    // Free resources
    imagedestroy($image);

    return $result;
}
?>
