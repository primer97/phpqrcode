<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use primer\phpqrcode\QRcode;
use primer\phpqrcode\QRConstants;
use primer\phpqrcode\QRSettings;
use Zxing\QrReader;

class QRcodeJpgTest extends TestCase
{
    
    private static function buildPath(string $filename):string
    {
        return __DIR__."/out/$filename.jpg";
    }
    public function setup(): void
    {
        QRSettings::default();
    }
    public function testBuildNumericJpg()
    {
        $file=self::buildPath('numeric');
        QRcode::jpg("123456",$file,QRConstants::QR_ECLEVEL_H,5,5,80);
        $this->assertTrue(file_exists($file));
    }
    
    public function testReadNumericECLow()
    {
        $file=$file=self::buildPath('numeric_lowQual');
        $in="1234567890123";
        QRcode::jpg($in,$file,QRConstants::QR_ECLEVEL_L,5,5,60);
        $this->assertTrue(file_exists($file));
        $reader = new QrReader($file);
        $out=$reader->text();
        $this->assertSame($in,$out);
    }
    
}
