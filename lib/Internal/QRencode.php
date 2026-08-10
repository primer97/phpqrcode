<?php
/*
 * PHP QR Code encoder
 *
 * Main encoder classes.
 *
 * Based on libqrencode C library distributed under LGPL 2.1
 * Copyright (C) 2006, 2007, 2008, 2009 Kentaro Fukuchi <fukuchi@megaui.net>
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

namespace primer\phpqrcode\Internal;


use Exception;
use primer\phpqrcode\QRcode;
use primer\phpqrcode\QRConstants;
use primer\phpqrcode\QRimage;
use primer\phpqrcode\QRSettings;
use primer\phpqrcode\QRtools;

/**
 * @internal
 */
class QRencode
{
    
    public bool $casesensitive = true; //todo create a settings ?
    
    public int $version = 0;
    public int $size    = 3;
    public int $margin  = 4;
    
    /** @var int $level error correction level 0 to 3 */
    public int $level = QRConstants::QR_ECLEVEL_L;
    public int $hint  = QRConstants::QR_MODE_8;
    
    private function isEightBit():bool
    {
        return QRSettings::isForcedMode() &&
            ( QRSettings::getForcedMode() == QRConstants::QR_MODE_8
              || QRSettings::getForcedMode() == QRConstants::QR_MODE_KANJI);
    }
    
    /**
     * @param int $level Error correction level
     * @param int $size pixel size
     * @param int $margin silent zone size
     * @return QRencode
     */
    public static function factory(int $level = QRConstants::QR_ECLEVEL_L, int $size = 3, int $margin = 4):QRencode
    {
        $enc         = new QRencode();
        $enc->size   = $size;
        $enc->margin = $margin;
        
        switch($level)
        {
            case 0:
            case 1:
            case 2:
            case 3:
                $enc->level = $level;
                break;
            default:
                $enc->level = QRConstants::QR_ECLEVEL_L;
                break;
        }
        
        return $enc;
    }
    
    /**
     * @throws Exception
     */
    public function encodeRAW(string $intext):array
    {
        $code = new QRcode();
        
        if($this->isEightBit())
        {
            $code->encodeString8bit($intext, $this->version, $this->level);
        }
        else
        {
            $code->encodeString($intext, $this->version, $this->level, $this->hint, $this->casesensitive);
        }
        
        return $code->data; //todo build a getter
    }
    
    /**
     * @param string $intext
     * @param ?string   $outfile
     * @return array<string>|void
     * @throws Exception
     */
    public function encode(string $intext, ?string $outfile = null) //void|array
    {
        $code = new QRcode();
        
        if($this->isEightBit())
        {
            $code->encodeString8bit($intext, $this->version, $this->level);
        }
        else
        {
            $code->encodeString($intext, $this->version, $this->level, $this->hint, $this->casesensitive);
        }
        
        if(!is_null($outfile))
        {
            file_put_contents($outfile, join("\n", QRtools::binarize($code->data)));
        }
        else
        {
            return QRtools::binarize($code->data);
        }
    }
    
    /**
     * @param array<string> $tab
     * @return int
     */
    private function getRealPointSize(array $tab):int
    {
        $maxSize = (int)(QRConstants::QR_PNG_MAXIMUM_SIZE/(count($tab) + 2*$this->margin));
        return min(max(1, $this->size), $maxSize);
    }

    public function encodePNG(string $intext, ?string $outfile = null, bool $sendToBrowser = false):void
    {
        try
        {
            
            ob_start();
            $tab = $this->encode($intext); //todo it may raise an Exception here (empty string) then ob_end_clean() is not proceeded. we have to fix that.
            $err = ob_get_contents();
            ob_end_clean();
            
            if($err != '')
                QRtools::log($outfile, $err);
            
            QRimage::png($tab, $outfile, $this->getRealPointSize($tab), $this->margin, $sendToBrowser);
            
        } catch(Exception $e)
        {
            
            QRtools::log($outfile, $e->getMessage());
            
        }
    }
    
    public function encodeJPG(string $intext, ?string $outfile = null, int $jpgQual=85):void
    {
        try
        {
            
            ob_start();
            $tab = $this->encode($intext); //todo it may raise an Exception here (empty string) then ob_end_clean() is not proceeded. we have to fix that.
            $err = ob_get_contents();
            ob_end_clean();
            
            if($err != '')
                QRtools::log($outfile, $err);
            
            QRimage::jpg($tab, $outfile, $this->getRealPointSize($tab), $this->margin,$jpgQual);
        } catch(Exception $e)
        {
            
            QRtools::log($outfile, $e->getMessage());
            
        }
    }
    
}
