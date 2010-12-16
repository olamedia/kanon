<?php


/**
 * Description of kanonFile
 *
 * @author olamedia
 */
class fileCache {
    public function getContents($filename, $cacheFilename, $context = null){
        if (is_file($cacheFilename)){
            return file_get_contents($cacheFilename);
        }
        $contents = file_get_contents($filename, 0, $context);
        file_put_contents($cacheFilename, $contents);
        return $contents;
    }
}

