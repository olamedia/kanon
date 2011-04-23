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

$fs = yHtmlTag::create('fieldset');
$fs['class'] = 'form-layout-fieldset';
if (strlen($this->_label)){
    $fs->appendChild(
            yHtmlTag::create('legend')
                    ->appendChild(
                            // span here for allowing advanced styling
                            yHtmlTag::create('span')
                            ->setText($this->_label)
                    )
    );
}
$list = in_array($this->_blockContainer, array('ol', 'ul', 'li'));
$listTagName = ($this->_blockContainer === 'ol'?'ol':'ul');
if ($list){
    $container = yHtmlTag::create($listTagName);
    $fs->appendChild($container);
}else{
    $container = $fs;
}
foreach ($this->_controls as $name=>$c){
    $row = yHtmlTag::create(
                    ($list?'li':$this->_blockContainer), array('class'=>'form-input')
    );
    $row->getAttribute('class')->setDelimiter(' '); // separate classes by space
    $control = $this->getControl($name);
    $label = $control->getLabel();
    if ($control->isRequired()){
        $row->getAttribute('class')->push('input-required');
        $label->appendChild(yHtmlTag::create('abbr', array('title'=>$this->_requiredTitle))->setText('*'));
    }else{
        $row->getAttribute('class')->push('input-optional');
    }
    $labelCell = yHtmlTag::create('td');
    if ($label->hasChildNodes()){ // ex. textNode
        $row->appendChild($label);
    }
    $row->appendChild($control);
    $errors = $control->getErrorMessages();
    if (count($errors)){
        $row['class']->push('input-invalid');
        $errorList = yHtmlTag::create('ul', array('class'=>'input-errors'));
        $row->appendChild($errorList);
        foreach ($errors as $message){
            $errorList->appendChild(
                    yHtmlTag::create('li')->appendChild(
                            yHtmlTag::create(
                                            'strong', array('class'=>'input-error')
                                    )
                                    ->setText($message)
                    )
            );
        }
    }
    $help = $control->getHelpHtml();
    if (strlen($help)){
        $row->appendChild(yHtmlTag::create('em', array('class'=>'input-help'))->appendChild($help));
    }
    $container->appendChild($row);
}

return $fs;

