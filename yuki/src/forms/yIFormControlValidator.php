<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 *
 * @author olamedia
 */
interface yIFormControlValidator{
    public function __construct($options = array());
    public function validate($control);
}
