<?php

/*
 * This file is part of the yuki package.
 * Copyright (c) 2011 olamedia <olamedia@gmail.com>
 *
 * Licensed under The MIT License
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * yDirectory
 *
 * @package yuki
 * @subpackage file
 * @author olamedia
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
class yDirectory extends yFilesystemResource{
    public function upload($tmp){
        // Check if directory still exists
        if (is_dir($this->getPath())){
            throw new Exception('Can\'t upload - filename used by directory');
        }
        parent::upload($tmp);
    }
}

