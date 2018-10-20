<?php


//-- Custom function to get related video thumbnail
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