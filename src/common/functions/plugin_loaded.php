<?php
function plugin_loaded($pluginName){
	return plugins::isLoaded($pluginName);
}