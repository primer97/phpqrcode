<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use primer\phpqrcode\QRcode;
use primer\phpqrcode\QRConstants;
use primer\phpqrcode\QRSettings;
use Zxing\QrReader;

class QRcodePngTest extends TestCase
{
    
    private static function buildPath(string $filename):string
    {
        return __DIR__."/out/$filename.png";
    }
    public function setup(): void
    {
        QRSettings::default();
    }
    public function testBuildNumericPng()
    {
        $file=self::buildPath('numeric');
        QRcode::png("123456",$file,QRConstants::QR_ECLEVEL_L);
        $this->assertTrue(file_exists($file));
    }
    
//region check QR content
    public function testReadNumericECLow()
    {
        $file=$file=self::buildPath('numeric_low');
        $in="1234567890123";
        QRcode::png($in,$file,QRConstants::QR_ECLEVEL_L);
        $this->assertTrue(file_exists($file));
        $reader = new QrReader($file);
        $out=$reader->text();
        $this->assertSame($in,$out);
    }
    public function testReadNumericECHigh()
    {
        $file=self::buildPath('numeric_high');
        $in="1234567890123";
        QRcode::png($in,$file,QRConstants::QR_ECLEVEL_H);
        $this->assertTrue(file_exists($file));
        $reader = new QrReader($file);
        $out=$reader->text();
        $this->assertSame($in,$out);
    }
    
    public function testEncodeAndReReadExtendChars()
    {
        $file=self::buildPath('extchars');
        $in=" &aA|\\%/*-+.,^'#~?!<>[]`\n\r\"\t{}@;:§¤°é$";
        QRcode::png($in,$file);
        $this->assertTrue(file_exists($file));
        $reader = new QrReader($file);
        $out=$reader->text();
        $this->assertSame($in,$out);
    }
    
    public function testEncodeAndReReadUtf8_Auto()
    {
        $file=self::buildPath('utf8_auto');
        $in="★→⚡✅";
        QRcode::png($in,$file);
        $this->assertTrue(file_exists($file));
        $reader = new QrReader($file);
        $out=$reader->text();
        $this->assertSame($in,$out);
    }
    public function testEncodeAndReReadUtf8_Forced()
    {
        $file=self::buildPath('utf8_forced');
        $in="★→⚡✅";
        QRSettings::forceMode(QRConstants::QR_MODE_8);
        QRcode::png($in,$file);
        $this->assertTrue(file_exists($file));
        $reader = new QrReader($file);
        $out=$reader->text();
        $this->assertSame($in,$out);
    }
    
    public function testEncodeAndReReadKanji_Auto()
    {
        $file=self::buildPath('kanji_auto');
        $in="漢字体";
        QRcode::png($in,$file);
        $this->assertTrue(file_exists($file));
        $reader = new QrReader($file);
        $out=$reader->text();
        $this->assertSame($in,$out);
    }
    public function testEncodeAndReReadKanji_Forced()
    {
        $file=self::buildPath('kanji_forced');
        $in="漢字体";
        QRSettings::forceMode(QRConstants::QR_MODE_KANJI);
        QRcode::png($in,$file);
        $this->assertTrue(file_exists($file));
        $reader = new QrReader($file);
        $out=$reader->text();
        $this->assertSame($in,$out);
    }
//endregion check QR content
    
    public function testPngDefaultMask()
    {
        $file=$file=self::buildPath('numeric_mask1');
        QRSettings::setDefaultMask(1);
        QRcode::png("123456",$file);
        $this->assertTrue(file_exists($file));
    }
    public function testPngBestCount()
    {
        $file=$file=self::buildPath('numeric_best3');
        QRSettings::setFindBestMask(3);
        QRcode::png("123456",$file);
        $this->assertTrue(file_exists($file));
    }

}
