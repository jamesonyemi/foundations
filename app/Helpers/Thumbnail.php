<?php

namespace App\Helpers;

class Thumbnail
{
    public static function generate_image_thumbnail($source_image_path, $thumbnail_image_path, $thumbnail_with = 160, $thumbnail_height = 160)
    {
        list($source_image_width, $source_image_height, $source_image_type) = getimagesize($source_image_path);
        switch ($source_image_type) {
            case IMAGETYPE_GIF:
                $source_gd_image = imagecreatefromgif($source_image_path);
                break;
            case IMAGETYPE_JPEG:
                $source_gd_image = imagecreatefromjpeg($source_image_path);
                break;
            case IMAGETYPE_PNG:
                $source_gd_image = imagecreatefrompng($source_image_path);
                break;
        }
        if ($source_gd_image === false) {
            return false;
        }
        $source_aspect_ratio = $source_image_width / $source_image_height;
        $thumbnail_aspect_ratio = $thumbnail_with / $thumbnail_height;
        if ($source_image_width <= $thumbnail_with && $source_image_height <= $thumbnail_height) {
            $thumbnail_image_width = $source_image_width;
            $thumbnail_image_height = $source_image_height;
        } elseif ($thumbnail_aspect_ratio > $source_aspect_ratio) {
            $thumbnail_image_width = (int) ($thumbnail_with * $source_aspect_ratio);
            $thumbnail_image_height = $thumbnail_height;
        } else {
            $thumbnail_image_width = $thumbnail_with;
            $thumbnail_image_height = (int) ($thumbnail_height / $source_aspect_ratio);
        }
        $thumbnail_gd_image = imagecreatetruecolor($thumbnail_image_width, $thumbnail_image_height);
        switch ($source_image_type) {
            case IMAGETYPE_GIF:
                imagecopyresampled($thumbnail_gd_image, $source_gd_image, 0, 0, 0, 0, $thumbnail_image_width, $thumbnail_image_height, $source_image_width, $source_image_height);
                $background = imagecolorallocate($thumbnail_gd_image, 0, 0, 0);
                imagecolortransparent($thumbnail_gd_image, $background);
                @imagegif($thumbnail_gd_image, $thumbnail_image_path);
                break;
            case IMAGETYPE_JPEG:
                imagecopyresampled($thumbnail_gd_image, $source_gd_image, 0, 0, 0, 0, $thumbnail_image_width, $thumbnail_image_height, $source_image_width, $source_image_height);
                imagejpeg($thumbnail_gd_image, $thumbnail_image_path, 90);
                break;
            case IMAGETYPE_PNG:
                imagealphablending($thumbnail_gd_image, false);
                imagesavealpha($thumbnail_gd_image, true);
                imagecopyresampled($thumbnail_gd_image, $source_gd_image, 0, 0, 0, 0, $thumbnail_image_width, $thumbnail_image_height, $source_image_width, $source_image_height);
                @imagepng($thumbnail_gd_image, $thumbnail_image_path);
                break;
        }
        imagedestroy($source_gd_image);
        imagedestroy($thumbnail_gd_image);

        return true;
    }
}
