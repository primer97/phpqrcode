<?php

namespace primer\phpqrcode;

use PHPUnit\Framework\TestCase;

class QRcodeTest extends TestCase
{
    
    public function testPng()
    {
        $file=__DIR__."/out/qr1.png";
        QRcode::png("123456",$file,QRConstants::QR_ECLEVEL_L);
        $this->assertTrue(file_exists($file));
        
    }
}
