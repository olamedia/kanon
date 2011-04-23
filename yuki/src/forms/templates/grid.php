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

$table = yHeadTag::create('table');
$table['class'] = 'form-layout-grid';
$headerRow = yHtmlTag::create('tr');
$dataRow = yHtmlTag::create('tr');
$table->appendChild($headerRow);
$table->appendChild($dataRow);
foreach ($this->_controls as $name=>$c){
    $control = $this->getControl($name);
    $label = $control->getLabel();
    if ($control->isRequired()){
        $label->appendChild(yHtmlTag::create('abbr', array('title'=>$this->_requiredTitle))->setText('*'));
    }
    $labelCell = yHtmlTag::create('th');
    if ($label->hasChildNodes()){ // ex. textNode
        $labelCell->appendChild($label);
    }
    $headerRow->appendChild($labelCell);
    $dataRow->appendChild(yHtmlTag::create('td')->appendChild($control));
}

return $table;

