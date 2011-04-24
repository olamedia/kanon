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

require dirname(__FILE__).'/../kanon/kanon.php';
/**
 * Make thumbnail, max size 650px
 */
$app = new thumbnailer();
$app->setBaseUri('/');
$app->setMaxSize(650);
$app->run();
