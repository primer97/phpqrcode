<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use primer\phpqrcode\QRcode;
use primer\phpqrcode\QRConstants;
use primer\phpqrcode\QRSettings;
use primer\phpqrcode\QRtools;
use Zxing\QrReader;

class QRcodeCacheTest extends TestCase
{
    
    private static function buildPath(string $filename):string
    {
        return __DIR__."/out/$filename.png";
    }
    public function setup(): void
    {
        QRSettings::default();
    }
    public function testSetupFolderNope()
    {
        QRSettings::allowCache('');
        $this->assertFalse(QRSettings::isCacheActive());
    }
    
    public function testSetupFolderYep()
    {
        QRSettings::allowCache(__DIR__.'/out/cache');
        $this->assertTrue(QRSettings::isCacheActive());
        $this->assertTrue(str_contains(QRSettings::getCacheDir(),'/out/cache'.DIRECTORY_SEPARATOR));
//        $this->assertTrue(is_dir(QRSettings::getCacheDir()));
    }
    
    public function testCreateCacheFiles()
    {
        QRSettings::allowCache(__DIR__.'/out/cache');
        if(QRSettings::isCacheActive() && file_exists(QRSettings::getCacheDir().'frame_1.png'))
        {
            unlink(QRSettings::getCacheDir().'frame_1.png');
        }
        QRTools::buildCache();
        $this->assertTrue(QRSettings::isCacheActive());
        $this->assertTrue(file_exists(QRSettings::getCacheDir().'frame_1.png'));
        $this->assertTrue(file_exists(QRSettings::getCacheDir().'frame_40.png'));
    }
    
    public function testBuildQrWithActiveCache()
    {
        QRSettings::allowCache(__DIR__.'/out/cache');
        if(QRSettings::isCacheActive() && !file_exists(QRSettings::getCacheDir().'frame_1.png'))
        {
            QRTools::buildCache();
        }
        
        $file=self::buildPath('from_cache');
        $in="Hello, I was built with cache!";
        QRcode::png($in,$file,QRConstants::QR_ECLEVEL_M);
        $this->assertTrue(file_exists($file));
        $reader = new QrReader($file);
        $out=$reader->text();
        $this->assertSame($in,$out);
    }
    
}
