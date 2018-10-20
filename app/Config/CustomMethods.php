<?php


//-- Custom function to get related video thumbnail
function getVideoThumbnail($video){
    $myDirectory = opendir(public_path('uploads/thumbnails/'));
    $thumbnail="";
    while($entryName = readdir($myDirectory)) {
        $arrName=explode(".",$video);
        preg_match('/^'.$arrName[0].'+/', $entryName, $matches);
        if (!empty($matches)) {
            $thumbnail=$entryName;
        }
    }
    closedir($myDirectory);
    return $thumbnail;
}