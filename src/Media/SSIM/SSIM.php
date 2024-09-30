<?php

namespace InstagramAPI\Media\SSIM;

class SSIM
{
    public static function getSsimAndMsssim(
        $inputImage,
        $processedImage,
    ) {
        $ssim_index = self::ssim($inputImage, $processedImage);
        $msssim_index = self::msssim($inputImage, $processedImage);

        return [$ssim_index, $msssim_index];
    }

    public static function ssim(
        $inputImage,
        $processedImage,
    ) {
        $width = imagesx($inputImage);
        $height = imagesy($inputImage);

        $k1 = 0.01;
        $k2 = 0.03;
        $L = 255;

        $C1 = pow($k1 * $L, 2);
        $C2 = pow($k2 * $L, 2);

        $meanInput = self::_imagegrayscaledistribution($inputImage);
        $meanProcessed = self::_imagegrayscaledistribution($processedImage);
        $covariance = self::_covariance($inputImage, $processedImage, $meanInput, $meanProcessed);

        $variance1 = self::_imagegrayscaledistribution($inputImage, true) - pow($meanInput, 2);
        $variance2 = self::_imagegrayscaledistribution($processedImage, true) - pow($meanProcessed, 2);

        $numerator = (2 * $meanInput * $meanProcessed + $C1) * (2 * $covariance + $C2);
        $denominator = ($meanInput * $meanInput + $meanProcessed * $meanProcessed + $C1) * ($variance1 + $variance2 + $C2);

        return $numerator / $denominator;
    }

    public static function msssim(
        $inputImage,
        $processedImage,
        $scale = 5,
    ) {
        $msssim = 0;
        $weight = 1 / $scale;
        for ($i = 0; $i < $scale; $i++) {
            $ssim = self::ssim($inputImage, $processedImage);
            $msssim += pow($ssim, $weight);
            $inputImage = imagecreatetruecolor((int) (imagesx($inputImage) / 2), (int) (imagesy($inputImage) / 2));
            $processedImage = imagecreatetruecolor((int) (imagesx($processedImage) / 2), (int) (imagesy($processedImage) / 2));
            imagecopyresampled($inputImage, $inputImage, 0, 0, 0, 0, (int) (imagesx($inputImage) / 2), (int) (imagesy($inputImage) / 2), imagesx($inputImage), imagesy($inputImage));
            imagecopyresampled($processedImage, $processedImage, 0, 0, 0, 0, (int) (imagesx($processedImage) / 2), (int) (imagesy($processedImage) / 2), imagesx($processedImage), imagesy($processedImage));
        }

        return $msssim / $scale;
    }

    protected static function _covariance(
        $inputImage,
        $processedImage,
        $meanInput,
        $meanProcessed,
    ) {
        $width = imagesx($inputImage);
        $height = imagesy($inputImage);
        $covariance = 0;

        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $gray1 = (@imagecolorat($inputImage, $x, $y) >> 16) & 0xFF;
                $gray2 = (@imagecolorat($processedImage, $x, $y) >> 16) & 0xFF;
                $covariance += ($gray1 - $meanInput) * ($gray2 - $meanProcessed);
            }
        }

        return $covariance / ($width * $height);
    }

    protected static function _imagegrayscaledistribution(
        $img,
        $return_variance = false,
    ) {
        $sum = 0;
        $variance_sum = 0;
        $width = imagesx($img);
        $height = imagesy($img);

        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $gray = (imagecolorat($img, $x, $y) >> 16) & 0xFF;
                $sum += $gray;
                $variance_sum += $gray * $gray;
            }
        }

        $mean = $sum / ($width * $height);
        if ($return_variance) {
            $variance = $variance_sum / ($width * $height);

            return $variance;
        }

        return $mean;
    }
}
