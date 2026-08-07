<?php

namespace primer\phpqrcode\Internal;

use Exception;

/**
 * @internal
 */
class QRrawcode
{
    public $version;
    /** @var array<int>|null $datacode */
    public $datacode = [];
    /** @var array<int> $ecccode  */
    public $ecccode  = [];
    /** @var int $blocks */
    public $blocks;
    /** @var array<int, QRrsblock >  */
    public $rsblocks = [];
    /** @var int $count */
    public $count;
    /** @var int $dataLength */
    public $dataLength;
    /** @var int  */
    public $eccLength;
    /** @var int $b1 */
    public $b1;
    
    //----------------------------------------------------------------------
    public function __construct(QRinput $input)
    {
        $spec = [0, 0, 0, 0, 0];
        
        $this->datacode = $input->getByteStream(); //todo : do not store into $this->datacode when null
        if(is_null($this->datacode))
        {
            throw new Exception('null input string');
        }
        
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
        if(is_null($this->datacode)) return -1; // todo remove as soon as null is not allowed
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
        
        if($rs == null) return -1;
        if(is_null($this->datacode)) return -1; // todo remove as soon as null is not allowed
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
    
    //----------------------------------------------------------------------
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