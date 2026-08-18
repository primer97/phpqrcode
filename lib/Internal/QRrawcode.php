<?php

namespace primer\phpqrcode\Internal;

use Exception;

/**
 * @internal
 */
class QRrawcode
{
    public int $version;
    /** @var array<int> $datacode */
    public array $datacode = [];
    /** @var array<int> $ecccode  */
    public array $ecccode  = [];
    
    public int $blocks;
    
    /** @var array<int, QRrsblock >  */
    public array $rsblocks = [];
    
    public int $count;
    public int $dataLength;
    public int $eccLength;
    public int $b1;
    
    /**
     * @throws Exception
     */
    public function __construct(QRinput $input)
    {
        $spec = [0, 0, 0, 0, 0];
        
        $datacode = $input->getByteStream();
        if(is_null($datacode))
        {
            throw new Exception('null input string');
        }
        $this->datacode=$datacode;
        
        QRspec::getEccSpec($input->getVersion(), $input->getErrorCorrectionLevel(), $spec);
        
        $this->version    = $input->getVersion();
        $this->b1         = QRspec::rsBlockNum1($spec);
        $this->dataLength = QRspec::rsDataLength($spec);
        $this->eccLength  = QRspec::rsEccLength($spec);
        $this->ecccode    = array_fill(0, $this->eccLength, 0);
        $this->blocks     = QRspec::rsBlockNum($spec);
        
        $ret = $this->init($spec);
        if($ret < 0)
        {
            throw new Exception('block alloc error');
        }
        
        $this->count = 0;
    }
    
    /**
     * @param array<int> $spec
     * @return int
     */
    private function init(array $spec):int
    {
        $dl = QRspec::rsDataCodes1($spec);
        $el = QRspec::rsEccCodes1($spec);
        $rs = QRrs::init_rs(8, 0x11d, 0, 1, $el, 255 - $dl - $el);
        
        
        $blockNo = 0;
        $dataPos = 0;
        $eccPos  = 0;
        
        for($i = 0; $i < QRspec::rsBlockNum1($spec); $i++)
        {
            $ecc                      = array_slice($this->ecccode, $eccPos);
            $this->rsblocks[$blockNo] = new QRrsblock($dl, array_slice($this->datacode, $dataPos), $el, $ecc, $rs);
            $this->ecccode            = array_merge(array_slice($this->ecccode, 0, $eccPos), $ecc);
            
            $dataPos += $dl;
            $eccPos  += $el;
            $blockNo++;
        }
        
        if(QRspec::rsBlockNum2($spec) == 0)
            return 0;
        
        $dl = QRspec::rsDataCodes2($spec);
        $el = QRspec::rsEccCodes2($spec);
        $rs = QRrs::init_rs(8, 0x11d, 0, 1, $el, 255 - $dl - $el);
        
        if(is_null($rs)) return -1;
        
        for($i = 0; $i < QRspec::rsBlockNum2($spec); $i++)
        {
            $ecc                      = array_slice($this->ecccode, $eccPos);
            $this->rsblocks[$blockNo] = new QRrsblock($dl, array_slice($this->datacode, $dataPos), $el, $ecc, $rs);
            $this->ecccode            = array_merge(array_slice($this->ecccode, 0, $eccPos), $ecc);
            
            $dataPos += $dl;
            $eccPos  += $el;
            $blockNo++;
        }
        
        return 0;
    }
    
    public function getCode()
    {
        if($this->count < $this->dataLength)
        {
            $row = $this->count%$this->blocks;
            $col = $this->count/$this->blocks;
            if($col >= $this->rsblocks[0]->dataLength)
            {
                $row += $this->b1;
            }
            $ret = $this->rsblocks[$row]->data[$col];
        }
        else if($this->count < $this->dataLength + $this->eccLength)
        {
            $row = ($this->count - $this->dataLength)%$this->blocks;
            $col = ($this->count - $this->dataLength)/$this->blocks;
            $ret = $this->rsblocks[$row]->ecc[$col];
        }
        else
        {
            return 0;
        }
        $this->count++;
        
        return $ret;
    }
}