<?php
/*
 * PHP QR Code encoder
 *
 * Image output of code using GD2
 *
 * PHP QR Code is distributed under LGPL 3
 * Copyright (C) 2010 Dominik Dzienia <deltalab at poczta dot fm>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */

namespace primer\phpqrcode;

class QRimage
{
    
    /**
     * @param array<array<string>> $frame
     * @param string|null $filename
     * @param int         $pixelPerPoint
     * @param int         $outerFrame
     * @param bool        $sendToBrowser
     * @return void
     */
    public static function png(array $frame, ?string $filename = null, int $pixelPerPoint = 4, int $outerFrame = 4, bool $sendToBrowser = false):void
    {
        $image = self::image($frame, $pixelPerPoint, $outerFrame);
        
        if(is_null($filename))
        {
            header("Content-type: image/png");
            imagepng($image);
        }
        else
        {
            if($sendToBrowser === true)
            {
                imagepng($image, $filename);
                header("Content-type: image/png");
                imagepng($image);
            }
            else
            {
                imagepng($image, $filename);
            }
        }
        
        imagedestroy($image);
    }
    
    
    /**
     * @param array<array<string>>  $frame
     * @param string|null $filename
     * @param int         $pixelPerPoint
     * @param int         $outerFrame
     * @param int         $q
     * @return void
     */
    public static function jpg(array $frame, ?string $filename = null, int $pixelPerPoint = 8, int $outerFrame = 4, int $q = 85):void
    {
        $image = self::image($frame, $pixelPerPoint, $outerFrame);
        
        if(is_null($filename))
        {
            header("Content-type: image/jpeg");
            imagejpeg($image, null, $q);
        }
        else
        {
            imagejpeg($image, $filename, $q);
        }
        
        imagedestroy($image);
    }
    
    /**
     * @param array $frame
     * @param int   $pixelPerPoint
     * @param int   $outerFrame
     * @return false|\GdImage|resource
     */
    private static function image(array $frame, int $pixelPerPoint = 4, int $outerFrame = 4)
    {
        $h = count($frame);
        $w = strlen($frame[0]);
        
        $imgW = $w + 2*$outerFrame;
        $imgH = $h + 2*$outerFrame;
        
        $base_image = imagecreate($imgW, $imgH);
        
        $col[0] = imagecolorallocate($base_image, 255, 255, 255);
        $col[1] = imagecolorallocate($base_image, 0, 0, 0);
        
        imagefill($base_image, 0, 0, $col[0]);
        
        for($y = 0; $y < $h; $y++)
        {
            for($x = 0; $x < $w; $x++)
            {
                if($frame[$y][$x] == '1')
                {
                    imagesetpixel($base_image, $x + $outerFrame, $y + $outerFrame, $col[1]);
                }
            }
        }
        
        $target_image = imagecreate($imgW*$pixelPerPoint, $imgH*$pixelPerPoint);
        imagecopyresized($target_image, $base_image, 0, 0, 0, 0, $imgW*$pixelPerPoint, $imgH*$pixelPerPoint, $imgW, $imgH);
        imagedestroy($base_image);
        
        return $target_image;
    }
}