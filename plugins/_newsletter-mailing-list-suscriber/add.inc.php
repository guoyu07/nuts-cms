<?php

/*@var $plugin Plugin */
include(PLUGIN_PATH.'/form.inc.php');

if($plugin->formValid())
{
	if($_POST['BatchMode'] == 'NO')
		$CUR_ID = $plugin->formInsert();
	else
		include(__DIR__.'/_trt-batch.inc.php');
	
	
}

