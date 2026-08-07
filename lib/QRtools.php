<?php
/*
 * PHP QR Code encoder
 *
 * Toolset, handy and debug utilites.
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


class QRtools
{
    
    /**
     * @param array<array<string>> $frame
     * @return array<array<string>>
     */
    public static function binarize(array $frame):array
    {
        $len = count($frame);
        foreach($frame as &$frameLine)
        {
            
            for($i = 0; $i < $len; $i++)
            {
                $frameLine[$i] = (ord($frameLine[$i]) & 1) ? '1' : '0';
            }
        }
        
        return $frame;
    }
    
    //----------------------------------------------------------------------
    public static function tcpdfBarcodeArray($code, $mode = 'QR,L', $tcPdfVersion = '4.5.037')
    {
        $barcode_array = [];
        
        if(!is_array($mode))
            $mode = explode(',', $mode);
        
        $eccLevel = 'L';
        
        if(count($mode) > 1)
        {
            $eccLevel = $mode[1];
        }
        
        $qrTab = QRcode::text($code, null, $eccLevel);
        $size  = count($qrTab);
        
        $barcode_array['num_rows'] = $size;
        $barcode_array['num_cols'] = $size;
        $barcode_array['bcode']    = [];
        
        foreach($qrTab as $line)
        {
            $arrAdd = [];
            foreach(str_split($line) as $char)
                $arrAdd[] = ($char == '1') ? 1 : 0;
            $barcode_array['bcode'][] = $arrAdd;
        }
        
        return $barcode_array;
    }
    
    //----------------------------------------------------------------------
    public static function clearCache():void
    {
//        self::$frames = [];
    }
    
    //----------------------------------------------------------------------
    public static function buildCache():void
    {
        $mask = new QRmask();
        for($a = 1; $a <= QRConstants::QRSPEC_VERSION_MAX; $a++)
        {
            $frame = QRspec::newFrame($a);
            if(QRConstants::QR_IMAGE)
            {
                $fileName = QRConstants::QR_CACHE_DIR.'frame_'.$a.'.png';
                QRimage::png(self::binarize($frame), $fileName, 1, 0);
            }
            
            $width   = count($frame);
            $bitMask = array_fill(0, $width, array_fill(0, $width, 0));
            for($maskNo = 0; $maskNo < 8; $maskNo++)
                $mask->makeMaskNo($maskNo, $width, $frame, $bitMask, true);
        }
        
    }
    
    /**
     * @param string|null $outfile
     * @param string $err Error message
     * @return void
     */
    public static function log(?string $outfile, string $err)
    {
        // error handler - for later, allow user callback
    }
    
    /**
     * @param array<array<int>> $frame
     * @return void
     */
    public static function dumpMask(array $frame):void
    {
        $width = count($frame);
        for($y = 0; $y < $width; $y++)
        {
            for($x = 0; $x < $width; $x++)
            {
                echo ord($frame[$y][$x]).',';
            }
        }
    }
    
}
