<?php
/* @var $plugin Plugin */
include(PLUGIN_PATH.'/form.inc.php');

$plugin->formInit();
if($plugin->formValid())
{
	$plugin->formUpdate();
}


