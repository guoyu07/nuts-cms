<?php

/* @var $plugin Plugin */
/* @var $nuts NutsCore */

// export ********************************************************
if(@$_GET['action'] == 'export')
{
	$_GET['ID'] = (int)@$_GET['ID'];

	$file_name = 'form_records_export';
	header("Content-type: application/csv, charset=UTF-8; encoding=UTF-8");
	header("Content-disposition: attachment; filename=\"$file_name.csv\"");


	$sql = "SELECT * FROM NutsFormData WHERE NutsFormID = {$_GET['ID']} AND Deleted = 'NO' ORDER BY ID ASC";
	$nuts->doQuery($sql);

	$init = false;
	while($row = $nuts->dbFetch())
	{
		echo $row['Date'].';'.$row['DataSerialize']."\n";
	}
	
	
	exit();

}

// clean ********************************************************
if(@$_GET['action'] == 'clean')
{
	$_GET['ID'] = (int)@$_GET['ID'];
	$nuts->doQuery("DELETE FROM NutsFormData WHERE NutsFormID = {$_GET['ID']}");
	exit();
}


// assign table to db
$plugin->listSetDbTable("NutsForm",

										"
											(SELECT COUNT(*) FROM NutsFormField WHERE NutsFormID = NutsForm.ID AND Deleted = 'NO') AS Fields,
											(SELECT COUNT(*) FROM NutsFormData WHERE NutsFormID = NutsForm.ID AND Deleted = 'NO') AS Export
										");

// search engine
$plugin->listSearchAddFieldText('ID');
$plugin->listSearchAddFieldSelectSql('Name', $lang_msg[2]);


// create fields
$plugin->listAddCol('ID', '', 'center; width:30px', true);
$plugin->listAddCol('Name', $lang_msg[2], '; width:30px; white-space:nowrap;', false); // with order by
$plugin->listAddCol('Fields', $lang_msg[21], 'center; width:30px; white-space:nowrap;', false);
$plugin->listAddCol('Description', $lang_msg[3], '', false);

$plugin->listAddCol('Export', ' ', 'center; width:30px; white-space:nowrap;', false);



// render list
$plugin->listRender(50, 'hookData');


function hookData($row)
{
	global $lang_msg;

	$row['Fields'] = <<<EOF
<img src="img/widget.png" align="absbottom" style="width:16px;" /> 
<a href="javascript:popupModal('/nuts/?mod=_form-builder-fields&do=list&popup=1&NutsFormID={$row['ID']}&NutsFormID_operator=_equal_&user_se=1');">{$row['Fields']}</a>

EOF;

	
	// export
	if($row['Export'] > 0)
	{
		$row['Export'] = '<span id="form_'.$row['ID'].'"><a class="tt" title="'.$lang_msg[27].'" href="/nuts/?mod=_form-builder&do=list&action=export&ID='.$row['ID'].'"><img src="img/icon-excel.png" align="absbottom" /> '.$row['Export'].'</a>';
		$row['Export'] .= ' <a class="tt" title="'.$lang_msg[33].'" href="javascript:cleanForm('.$row['ID'].');"><img style="width:12px;" src="img/list_delete.png" align="absmiddle" /></a></span>';
	}
	
	if($row['FormStockData'] == "NO")$row['Export'] = " - ";



	return $row;
}



?>