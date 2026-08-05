<?php

namespace primer\phpqrcode;

use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\TestCase;
use Zxing\QrReader;

class QRcodeTest extends TestCase
{
    use PHPMock;
    
    public function setup(): void
    {
        QRSettings::default();
    }
    public function testPng()
    {
        $file=__DIR__."/out/qr1.png";
        QRcode::png("123456",$file,QRConstants::QR_ECLEVEL_L);
        $this->assertTrue(file_exists($file));
    }
    
    public function testSentToBrowser()
    {
        // mock `header()` to prevent the "Cannot modify header information..." issue, and to check the right 'content-type' as well.
        $header = $this->getFunctionMock(
            'primer\\phpqrcode',
            'header'
        );
        
        $header->expects($this->once())
               ->with('Content-type: image/png');
        
        ob_start();
        QRcode::png("123456");
     $out=ob_get_clean();
     
     // And now ensure we receive a png image:
     $exp="\x89PNG\r\n\x1A\n"; // png image magic id
     $img=substr($out, 0, 8);
     $this->assertSame($exp,$img);
     
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
    
    public function testEncodeAndReReadUtf8_Auto()
    {
        $file=__DIR__."/out/qr4.png";
        $in="★→⚡✅";
        QRcode::png($in,$file,QRConstants::QR_ECLEVEL_L);
        $this->assertTrue(file_exists($file));
        $reader = new QrReader($file);
        $out=$reader->text();
        $this->assertSame($in,$out);
    }
    public function testEncodeAndReReadUtf8_Forced()
    {
        $file=__DIR__."/out/qr4.png";
        $in="★→⚡✅";
        QRSettings::forceMode(QRConstants::QR_MODE_8);
        QRcode::png($in,$file,QRConstants::QR_ECLEVEL_L);
        $this->assertTrue(file_exists($file));
        $reader = new QrReader($file);
        $out=$reader->text();
        $this->assertSame($in,$out);
    }
    
    public function testEncodeAndReReadKanji_Auto()
    {
        $file=__DIR__."/out/qr9.png";
        $in="漢字体";
        QRcode::png($in,$file,QRConstants::QR_ECLEVEL_L);
        $this->assertTrue(file_exists($file));
        $reader = new QrReader($file);
        $out=$reader->text();
        $this->assertSame($in,$out);
    }
    public function testEncodeAndReReadKanji_Forced()
    {
        $file=__DIR__."/out/qr9.png";
        $in="漢字体";
        QRSettings::forceMode(QRConstants::QR_MODE_KANJI);
        QRcode::png($in,$file,QRConstants::QR_ECLEVEL_L);
        $this->assertTrue(file_exists($file));
        $reader = new QrReader($file);
        $out=$reader->text();
        $this->assertSame($in,$out);
    }
//endregion check QR content
    
    public function testPngDefaultMask()
    {
        $file=__DIR__."/out/qr5.png";
        QRSettings::setDefaultMask(1);
        QRcode::png("123456",$file);
        $this->assertTrue(file_exists($file));
    }
    public function testPngBestCount()
    {
        $file=__DIR__."/out/qr6.png";
        QRSettings::setFindBestMask(3);
        QRcode::png("123456",$file);
        $this->assertTrue(file_exists($file));
    }

}
