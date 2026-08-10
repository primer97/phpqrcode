<?php
/*
 * PHP QR Code encoder
 *
 * Bitstream class
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

/**
 * @internal
 */
class QRbitstream
{
    /** @var array<int> $data  */
    protected array $data = [];
    
    public function size():int
    {
        return count($this->data);
    }
    
    private function allocate(int $setLength):void
    {
        $this->data = array_fill(0, $setLength, 0);
    }
    
    private static function newFromNum(int $bits, int $num):QRbitstream
    {
        $bstream = new QRbitstream();
        $bstream->allocate($bits);
        
        $mask = 1 << ($bits - 1);
        for($i = 0; $i < $bits; $i++)
        {
            if($num & $mask)
            {
                $bstream->data[$i] = 1;
            }
            else
            {
                $bstream->data[$i] = 0;
            }
            $mask = $mask >> 1;
        }
        
        return $bstream;
    }
    
    
    /**
     * @param int        $size
     * @param array<int> $data
     * @return QRbitstream
     */
    private static function newFromBytes(int $size, array $data):QRbitstream
    {
        $bstream = new QRbitstream();
        $bstream->allocate($size*8);
        $p = 0;
        
        for($i = 0; $i < $size; $i++)
        {
            $mask = 0x80;
            for($j = 0; $j < 8; $j++)
            {
                if($data[$i] & $mask)
                {
                    $bstream->data[$p] = 1;
                }
                else
                {
                    $bstream->data[$p] = 0;
                }
                $p++;
                $mask = $mask >> 1;
            }
        }
        
        return $bstream;
    }
    
    
    /**
     * @param QRbitstream|null $arg -- todo check if null possible ?
     * @return int -1 on error, 0 otherwise
     */
    public function append(?QRbitstream $arg):int
    {
        if(is_null($arg))
        {
            return -1;
        }
        
        if($arg->size() == 0)
        {
            return 0;
        }
        
        if($this->size() == 0)
        {
            $this->data = $arg->data;
            return 0;
        }
        
        $this->data = array_values(array_merge($this->data, $arg->data));
        
        return 0;
    }
    
    public function appendNum(int $bits, int $num):int
    {
        if($bits == 0)
            return 0;
        
        $b = QRbitstream::newFromNum($bits, $num);
        
        $ret = $this->append($b);
        unset($b);
        
        return $ret;
    }
    
    
    /**
     * @param int   $size
     * @param array<int> $data
     * @return int
     */
    public function appendBytes(int $size, array $data):int
    {
        if($size == 0)
            return 0;
        
        $b = QRbitstream::newFromBytes($size, $data);
        
        $ret = $this->append($b);
        unset($b);
        
        return $ret;
    }
    
    
    /**
     * @return array<int>
     */
    public function toByte():array
    {
        
        $size = $this->size();
        
        if($size == 0)
        {
            return [];
        }
        
        $data  = array_fill(0, (int)(($size + 7)/8), 0);
        $bytes = (int)($size/8);
        
        $p = 0;
        
        for($i = 0; $i < $bytes; $i++)
        {
            $v = 0;
            for($j = 0; $j < 8; $j++)
            {
                $v = $v << 1;
                $v |= $this->data[$p];
                $p++;
            }
            $data[$i] = $v;
        }
        
        if($size & 7)
        {
            $v = 0;
            for($j = 0; $j < ($size & 7); $j++)
            {
                $v = $v << 1;
                $v |= $this->data[$p];
                $p++;
            }
            $data[$bytes] = $v;
        }
        
        return $data;
    }
    
}
