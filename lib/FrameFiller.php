<?php

namespace primer\phpqrcode;

class FrameFiller
{
    
    public $width;
    /** @var array<string> $frame [y⇒[line]] */
    public $frame;
    public $x;
    public $y;
    public $dir;
    public $bit;
    
    /**
     * @param int $width
     * @param array<string> $frame
     */
    public function __construct(int $width, array &$frame)
    {
        $this->width = $width;
        $this->frame = $frame;
        $this->x     = $width - 1;
        $this->y     = $width - 1;
        $this->dir   = -1;
        $this->bit   = -1;
    }
    
    /**
     * @param array{'x':int, 'y':int} $at
     * @param int   $val
     * @return void
     */
    public function setFrameAt(array $at, int $val):void
    {
        // note $this->frame[i][pos] returns the char at position 'pos' from string $this->frame[i]
        $this->frame[$at['y']][$at['x']] = chr($val);
    }
    
    /**
     * @param array{'x':int, 'y':int} $at
     * @return int
     */
    public function getFrameAt(array $at):int
    {
        // note $this->frame[i][pos] returns the char at position 'pos' from string $this->frame[i]
        return ord($this->frame[$at['y']][$at['x']]);
    }
    
    /**
     * @return array{'x':int,'y':int}|null Next position
     */
    public function next()
    {
        do
        {
            
            if($this->bit == -1)
            {
                $this->bit = 0;
                return ['x' => $this->x, 'y' => $this->y];
            }
            
            $x = $this->x;
            $y = $this->y;
            $w = $this->width;
            
            if($this->bit == 0)
            {
                $x--;
                $this->bit++;
            }
            else
            {
                $x++;
                $y += $this->dir;
                $this->bit--;
            }
            
            if($this->dir < 0)
            {
                if($y < 0)
                {
                    $y         = 0;
                    $x         -= 2;
                    $this->dir = 1;
                    if($x == 6)
                    {
                        $x--;
                        $y = 9;
                    }
                }
            }
            else
            {
                if($y == $w)
                {
                    $y         = $w - 1;
                    $x         -= 2;
                    $this->dir = -1;
                    if($x == 6)
                    {
                        $x--;
                        $y -= 8;
                    }
                }
            }
            if($x < 0 || $y < 0) return null;
            
            $this->x = $x;
            $this->y = $y;
            
        }
        while(ord($this->frame[$y][$x]) & 0x80); // note $this->frame[y][x] returns the char at position 'x' from string $this->frame[y]
        
        return ['x' => $x, 'y' => $y];
    }
    
}