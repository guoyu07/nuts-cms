<?php
/**
 * Plugin gmaps-poi - action Edit
 * 
 * @version 1.0
 * @date 08/07/2013
 * @author H2lsoft (contact@h2lsoft.com) - http://www.h2lsoft.com
 */

/* @var $plugin Plugin */
/* @var $nuts NutsCore */
include(PLUGIN_PATH.'/form.inc.php');

$rec = $plugin->formInit();
if($plugin->formValid())
{
	$CUR_ID = $plugin->formUpdate();
}


?>