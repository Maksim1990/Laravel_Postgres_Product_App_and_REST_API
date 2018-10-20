<?php


//-- Custom function to get related video thumbnail
use Milon\Barcode\DNS1D;

function getVideoThumbnail($video){
    $folder='/uploads/thumbnails/';
    $myDirectory = opendir(public_path($folder));
    $thumbnail="";
    while($entryName = readdir($myDirectory)) {
        $arrName=explode(".",$video);
        preg_match('/^'.$arrName[0].'+/', $entryName, $matches);
        if (!empty($matches)) {
            $thumbnail=$folder.$entryName;
        }
    }
    closedir($myDirectory);
    return $thumbnail;
}


/**
 * Generate barcode EAN13 format
 * @param $length
 * @return string
 */
function generateBarcodeNumber($length) {

    //-- Generate unique barcode
    do{
        $result = generateCode($length);
    }while(\App\Product::where('barcode',$result)->first()!== null);

    return $result;
}

function generateCode($length){
    $result='';
    for($i = 0; $i < $length; $i++) {
        $result .= mt_rand(0, 9);
    }
    return $result;
}