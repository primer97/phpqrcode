<?php

namespace Tests;

use phpmock\phpunit\MockObjectProxy;
use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use primer\phpqrcode\QRcode;
use primer\phpqrcode\QRConstants;
use primer\phpqrcode\QRSettings;

class QRcodeSendToBrowserTest extends TestCase
{
    use PHPMock;
    
    const png_header="\x89PNG\r\n\x1A\n";
    
    /**
     * We mock `header()` to prevent the "Cannot modify header information..." issue.
     * This will help us to check the right 'content-type' as well.
     * @return MockObjectProxy|MockObject
     */
    private function mockHeader():MockObject
    {
        return $this->getFunctionMock(
            'primer\\phpqrcode',
            'header'
        );
    }
    private static function buildPath(string $filename):string
    {
        return __DIR__."/out/$filename.png";
    }
    public function setup(): void
    {
        QRSettings::default();
    }
    public function testLiveSendNoSavedFile()
    {
        $header = $this->mockHeader();
        $header->expects($this->once())
               ->with('Content-type: image/png');
        
        ob_start();
        QRcode::png("123456"); // no filename, so it automatically send to browser
        $out=ob_get_clean();
        
        // And now ensure we receive a png image:
        $exp=QRcodeSendToBrowserTest::png_header;
        $img=substr($out, 0, strlen(QRcodeSendToBrowserTest::png_header));
        $this->assertSame($exp,$img);
    }
    
    public function testLiveSendWithSavedFile()
    {
        $file=self::buildPath('liveSend');
        $header = $this->mockHeader();
        $header->expects($this->once())
               ->with('Content-type: image/png');
        
        ob_start();
        QRcode::png("123456",$file,QRConstants::QR_ECLEVEL_L,3,4,true);
        $out=ob_get_clean();
        
        // And now ensure we receive a png image:
        $exp=QRcodeSendToBrowserTest::png_header;
        $img=substr($out, 0, strlen(QRcodeSendToBrowserTest::png_header));
        $this->assertSame($exp,$img);
        
        //check file is created too
        $this->assertTrue(file_exists($file));
    }
    
}
