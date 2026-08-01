<?php

namespace primer\phpqrcode;

use PHPUnit\Framework\TestCase;
use Zxing\QrReader;

class QRcodeTest extends TestCase
{
    
    public function testPng()
    {
        $file=__DIR__."/out/qr1.png";
        QRcode::png("123456",$file,QRConstants::QR_ECLEVEL_L);
        $this->assertTrue(file_exists($file));
    }
    
//region check QR content
    public function testReadBasic()
    {
        $file=__DIR__."/out/qr2.png";
        $in="123456";
        QRcode::png($in,$file,QRConstants::QR_ECLEVEL_L);
        $this->assertTrue(file_exists($file));
        $reader = new QrReader($file);
        $out=$reader->text();
        $this->assertSame($in,$out);
    }
    
    public function testEncodeAndReReadExtendChars()
    {
        $file=__DIR__."/out/qr3.png";
        $in=" &aA|\\%/*-+.,^'#~?!<>[]`\n\r\"\t{}@;:§¤°é$";
        QRcode::png($in,$file,QRConstants::QR_ECLEVEL_L);
        $this->assertTrue(file_exists($file));
        $reader = new QrReader($file);
        $out=$reader->text();
        $this->assertSame($in,$out);
    }
    
    public function testEncodeAndReReadUtf8()
    {
        $file=__DIR__."/out/qr4.png";
        $in="★→⚡✅";
        QRcode::png($in,$file,QRConstants::QR_ECLEVEL_L);
        $this->assertTrue(file_exists($file));
        $reader = new QrReader($file);
        $out=$reader->text();
        $this->assertSame($in,$out);
    }
//endregion check QR content
}
