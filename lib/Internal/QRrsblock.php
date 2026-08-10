<?php

namespace primer\phpqrcode\Internal;

/**
 * @internal
 */
class QRrsblock
{
    public int $dataLength;
    
    /** @var array<int> $data */
    public array $data = [];
    
    public int $eccLength;
    
    /** @var array<int> $ecc */
    public array $ecc  = [];
    
    /**
     * @param int        $dl
     * @param array<int> $data
     * @param int        $el
     * @param array<int> $ecc
     * @param QRrsItem   $rs
     */
    public function __construct(int $dl, array $data, int $el, array &$ecc, QRrsItem $rs)
    {
        $rs->encode_rs_char($data, $ecc);
        
        $this->dataLength = $dl;
        $this->data       = $data;
        $this->eccLength  = $el;
        $this->ecc        = $ecc;
    }
}