<?php

/*
 * This file is part of the yuki package.
 * Copyright (c) 2011 olamedia <olamedia@gmail.com>
 *
 * This source code is release under the MIT License.
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once dirname(__FILE__).'/../src/autoload/yAutoloader.php';
require_once dirname(__FILE__).'/../src/autoload/yCoreAutoloader.php';

date_default_timezone_set('Asia/Yekaterinburg');

spl_autoload_register(array(yCoreAutoloader::getInstance(), 'autoload'));
