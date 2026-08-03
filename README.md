# phpqrcode
PHP QRCode Generator Library for php 7.3 and later.

A modern implementaion of QR code 2D barcode generator. 
Pure-php LGPL implementation based on inital phpqrcode, itself based on C libqrencode.

## Installation

Type on the terminal
`composer require primer/phpqrcode`

Or, by adding this entry on the composer.json file
```json
{
    "require": {
        "primer/phpqrcode": "~1"
   }
}
```

## Usage
Encode "data" into a png file:
```php
QRcode::png("data", "path/to/file.png");
```
![QR](./tests/out/qr.png)

## Options

```php
QRcode::png($text, $outfile , 
            correctionLevel: QRConstants::QR_ECLEVEL_L, 
            size: 3, 
            margin: 4, 
            saveandprint: false);
```

### Correction Level 
(int) correction level, fom the lowest to highest:
* `QRConstants::QR_ECLEVEL_L` 0
* `QRConstants::QR_ECLEVEL_M` 1
* `QRConstants::QR_ECLEVEL_Q` 2
* `QRConstants::QR_ECLEVEL_H` 3

### Size
(int) Pixel size
### Margin
(int) Raw silent zone (margin zone = raw * size)
### Save and Print
(bool) Use saveAndPrint (bool) option to send to the browser both http header and image content.


## Credits
- Kentaro Fukuchi: for inital libqrencode C Lib (kentaro@megaui.net)
- Dominik Dzienia: for https://phpqrcode.sourceforge.net (deltalab@poczta.fm)
 