<?php

/* @var $plugin Plugin */
/* @var $nuts NutsCore */


// assign table to db
$plugin->listSetDbTable('NutsGallery', "(SELECT COUNT(*) FROM NutsGalleryImage WHERE NutsGalleryImage.NutsGalleryID = NutsGallery.ID AND Deleted = 'NO') AS Total", "", "ORDER BY Position");

// create search engine
$plugin->listSearchAddFieldText('ID');
$plugin->listSearchAddFieldSelectSql('Name', $lang_msg[1]);

// create fields


// add list position
if(@!$_GET['popup'])
    $plugin->listAddColPosition('Position');

$plugin->listAddCol('Thumbnail', '&nbsp;', 'center; width:30px', false);
$plugin->listAddCol('Name', $lang_msg[1], '', false);
$plugin->listAddCol('Code', '');
$plugin->listAddCol('Total', $lang_msg[3], 'center; width:50px', false);
$plugin->listAddColImg('Active', $lang_msg[4], '', false);

// popup
if(@$_GET['popup'] == 1)
{
	$plugin->listAddCol('AddCode', '&nbsp;', 'center; width:35px');
}

$plugin->listRender(20, 'hookData');


function hookData($row)
{
	global $lang_msg, $plugin;

	$row['Code'] = "<pre>{@NUTS	TYPE='GALLERY'	NAME='{$row['Name']}'}</pre>";
	if(@$_GET['popup'] == 1)
	{
		$label = base64_encode($row['Name']);
		$code = "<p><img class=\"nuts_tags\" src=\"/nuts/img/icon_tags/tag.php?tag=gallery&label=$label\" title=\"{@NUTS    TYPE='GALLERY'    NAME='{$row['Name']}'}\" border=\"0\"></p>";
		$code = str_replace('"', '``', $code);
		$code = str_replace("'", "\\'", $code);

		$row['AddCode'] = '<a href="javascript:;" onclick="window.opener.WYSIWYGAddText(\''.$_GET['parentID'].'\', \''.$code.'\'); window.close();" class="tt" title="'.$lang_msg[7].'"><img src="img/icon-next.png" align=\"absmiddle\" /></a>';
	}

	$row['Position'] = $plugin->listGetPositionContents($row['ID']);


	$row['Thumbnail'] = '';
	if(!empty($row['LogoImage']))
	{
		$ext = explode('.', $row['LogoImage']);
		$ext = $ext[count($ext) - 1];

		$row['Thumbnail'] = '<img class="image_preview" src="'.NUTS_IMAGES_URL.'/gallery/thumb_'.$row['ID'].'.'.$ext.'?t='.time().'" style="height:65px; max-width:160px;" />';
	}
    else
    {
        $row['Thumbnail'] = '<img src="/nuts/img/no-preview.png" style="height:65px; max-width:160px;" />';
    }

	$row['Total'] = " <a href=\"javascript:popupModal('index.php?mod=_gallery_image&do=list&ID_operator=_equal_&ID=&NutsGalleryID_operator=_equal_&NutsGalleryID={$row['ID']}&user_se=1&popup=1', 'pops');\" class=\"tt\"><img src=\"img/icon-preview-mini.gif\" align=\"absmiddle\" alt=\"{$lang_msg[6]}\"> {$row['Total']}</a>";
	return $row;
}



?>