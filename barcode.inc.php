<?php
if (!defined('IN_DISCUZ')) {
    exit('Access denied');
}
global $_G;

define('LIBRARY_BARCODE_FONT', LIBRARY_ROOT . '/class/barcode/font/Arial.ttf');
// Including all required classes
require_once(LIBRARY_ROOT . '/class/barcode/BCGFontFile.php');
require_once(LIBRARY_ROOT . '/class/barcode/BCGColor.php');
require_once(LIBRARY_ROOT . '/class/barcode/BCGDrawing.php');

// Including the barcode technology
require_once(LIBRARY_ROOT . '/class/barcode/BCGean13.barcode.php');

// Loading Font
if (file_exists(LIBRARY_BARCODE_FONT)) {
    $font = new BCGFontFile(LIBRARY_BARCODE_FONT, 18);
}

// Don't forget to sanitize user inputs
$text = isset($_GET['text']) ? $_GET['text'] : '9787115279460';

// The arguments are R, G, B for color.
$color_black = new BCGColor(0, 0, 0);
$color_white = new BCGColor(255, 255, 255);

$drawException = null;
try {
    $code = new BCGean13();
    $code->setScale(2); // Resolution
    $code->setThickness(30); // Thickness
    $code->setForegroundColor($color_black); // Color of bars
    $code->setBackgroundColor($color_white); // Color of spaces
    if (file_exists(LIBRARY_BARCODE_FONT)) {
        $code->setFont($font); // Font (or 0)
    }
    $code->parse($text); // Text
} catch(Exception $exception) {
    $drawException = $exception;
}

// Here is the list of the arguments
// 1 - Filename (empty : display on screen)
// 2 - Background color
$drawing = new BCGDrawing('', $color_white);
if($drawException) {
    $drawing->drawException($drawException);
} else {
    $drawing->setBarcode($code);
    $drawing->draw();
}

// Header that says it is an image (remove it if you save the barcode to a file)
header('Content-Type: image/png');
header('Content-Disposition: inline; filename="barcode' . $text .'.png"');

// Draw (or save) the image into PNG format.
$drawing->finish(BCGDrawing::IMG_FORMAT_PNG);

