<?php
/*
 * @package Nuts
 */

/**
 * Crypt/Decrypt a string
 *
 * @param string $str
 * @param bool $crypt
 *
 * @return crypted string
 */
function nutsCrypt($str, $crypt=true)
{
	global $nuts;

	$qID = $nuts->dbGetQueryID();

	if($crypt)
	{
		$sql = "SELECT ENCODE('".addslashes($str)."', '".NUTS_CRYPT_KEY."') AS str";
	}
	else
	{
		$sql = "SELECT DECODE('".addslashes($str)."', '".NUTS_CRYPT_KEY."') AS str";
	}

	$nuts->doQuery($sql);


	$str = $nuts->dbGetOne();
	$nuts->dbSetQueryID($qID);



	return $str;
}




/**
 * Destroy nuts session and return in login page
 */
function nutsDestroyIt()
{
	$_COOKIE['NutsRemember'] = '';
	setcookie ("NutsRemember", "", time() - 3600);

	$_SESSION['NutsUserID'] = '';
	$_SESSION = array();
	unset($_SESSION);
	session_destroy();

	//header("Location: login.php");
	//exit();
	die("<script>document.location.href='login.php';</script>");
}

/**
 * Logout treatment
 */
function nutsLogout()
{
	nutsTrace('_system', 'logout', '');
	nutsDestroyIt();
}

/**
 * Return current date in gtml mode
 *
 * @return sql-datetime sql date => 'Y-m-d H:i:s'
 */
function nutsGetGMTDate()
{
	return gmdate('Y-m-d H:i:s');
}
/**
 * Return date in GMT
 *
 * @param sql-datetime $date
 * @param string $format php date desired format
 * @param string $output user|other (user => return date with its own timezone)
 * @return date
 */
function nutsGetGMTDateUser($date, $format='', $output='user')
{
	if($date == '0000-00-00 00:00:00' || $date == '0000-00-00')return '';
	return $date;


	/*if(empty($format))
	{
		$format = 'Y-m-d H:i:s';
		if(strlen($date) == 10)
			$format = 'Y-m-d';
	}

	$timezone = $_SESSION['Timezone'];

	$year   = substr($date, 0, 4);
	$month  = substr($date, 5, 2);
	$day    = substr($date, 8, 2);
	$hour   = substr($date, 11, 2);
	$minute = substr($date, 14, 2);
	$second = substr($date, 17, 2);

	$timestamp = mktime($hour, $minute, $second, $month, $day, $year);
	//Offset is in hours from gmt, including a - sign if applicable.
	//So lets turn offset into seconds
	$offset = $timezone * 60 * 60;

	if($output == 'user')
		$timestamp = $timestamp + $offset;
	else
		$timestamp = $timestamp - $offset;

	//Remember, adding a negative is still subtraction ;)
	$d = date($format, $timestamp);
	if(strlen($date) == 10)
		list($d) = explode(' ', $d);

	return $d;*/
}

/**
 * Trace event in log
 *
 * @param string $app
 * @param string $action
 * @param string $resume resume action default = connect
 * @param int $recordID default value = 0
 */
function nutsTrace($app, $action, $resume='connect', $recordID=0)
{
	$arr = array();
	$arr['NutsGroupID'] = (int)@$_SESSION['NutsGroupID'];
	$arr['NutsUserID'] = (int)@$_SESSION['ID'];
	$arr['DateGMT'] = nutsGetGMTDate();
	$arr['Application'] = $app;
	$arr['Action'] = $action;
	$arr['Resume'] = $resume;
	$arr['IP'] = $GLOBALS['nuts']->getIP();
	$arr['IP'] = ip2long($arr['IP']);
	$arr['RecordID'] = $recordID;

	$GLOBALS['nuts']->dbInsert('NutsLog', $arr);
}


/**
 * Return current defined theme
 *
 * @return $theme
 */
function nutsGetTheme()
{
	/*$GLOBALS['nuts']->doQuery("SELECT Theme FROM NutsTemplateConfiguration");
	$theme = $GLOBALS['nuts']->getOne();*/

	$theme = $GLOBALS['nuts_theme_selected'];

	return $theme;
}

/**
 * Return nuts distinct content type
 *
 * @param string $type select by default
 * @return $values
 */
function nutsGetOptionsContentType($type='select')
{
	$GLOBALS['nuts']->doQuery("SELECT
										Type
								FROM
										NutsContentType");

	$rows = $GLOBALS['nuts']->getData();
	if($type == 'select')
	{
		$options = '';
		foreach($rows as $type)
		{
			$options .= '<option value="'.$type['Type'].'">'.$type['Type'].'</option>'."\n";
		}

		return $options;
	}
}

/**
 * Get nuts users list
 *
 * @param type $type default = select
 * @return $values
 */
function nutsGetOptionsUsers($type='select')
{
	$GLOBALS['nuts']->doQuery("SELECT
										ID,
										CONCAT_WS(' ',UPPER(LastName), FirstName) AS Name,
										Deleted
								FROM
										NutsUser
								ORDER BY
										Deleted DESC");


	$rows = $GLOBALS['nuts']->getData();
	if($type == 'select')
	{
		$options = '';
		foreach($rows as $user)
		{
			$style = '';
			if(strtoupper($user['Deleted']) == 'YES')
				$style = ' style="background-color:red;"';

			$options .= '<option value="'.$user['ID'].'"'.$style.'">'.$user['Name'].'</option>'."\n";
		}

		return $options;
	}
}
/**
 * Get nuts distinct theme defined
 *
 * @param string $type default= select
 * @return $options
 */
function nutsGetOptionsTemplates($type='select')
{
	/*$GLOBALS['nuts']->doQuery("SELECT
										Theme
								FROM
										NutsTemplateConfiguration");
	$theme = $GLOBALS['nuts']->getOne();*/

	$theme = nutsGetTheme();

	$tpls = (array)glob(NUTS_THEMES_PATH.'/'.$theme.'/*.html');

	if($type == 'select')
	{
		$options = '';
		foreach($tpls as $tpl)
		{
			$tpl = str_replace(NUTS_THEMES_PATH.'/'.$theme.'/', '', $tpl);
			if($tpl != 'index.html' && $tpl[0] != '_')
			{
				$options .= '<option value="'.$tpl.'">'.$tpl.'</option>'."\n";
			}
		}
		return $options;
	}
}

/**
 * get all header image defined in folder library/images/header
 *
 * @param string $type default select
 * @return $values
 */
function nutsGetOptionsHeaderImage($type='select')
{
	$imgs = (array)glob(NUTS_HEADER_IMAGES_PATH.'/*.*');
	if($type == 'select')
	{
		$options = '';
		foreach($imgs as $img)
		{
			$img = str_replace(NUTS_HEADER_IMAGES_PATH.'/', '', $img);
			$ext = substr($img, strrpos($img, '.') + 1);
			if(in_array($ext, array('gif', 'png', 'jpg', 'swf')))
			{
				$options .= '<option value="'.$img.'">'.$img.'</option>'."\n";
			}
		}
		return $options;
	}
}

/**
 * Get nuts distinct languages defined
 *
 * @param string $type default= select
 * @return array|string $values
 */
function nutsGetOptionsLanguages($type='select')
{

	$GLOBALS['nuts']->doQuery("SELECT
										LanguageDefault,
										Languages
								FROM
										NutsTemplateConfiguration");
	$row = $GLOBALS['nuts']->dbFetch();
	$tab = explode(',', trim($row['Languages']));
	$tab = array_map('trim', $tab);

	if($type == 'select')
	{
		$options = '<option value="'.strtolower($row['LanguageDefault']).'">'.nutsGetLanguage(strtolower($row['LanguageDefault'])).'</option>'."\n";
		foreach($tab as $t)
			if(!empty($t))
				$options .= '<option value="'.$t.'">'.nutsGetLanguage($t).'</option>'."\n";
	}
	elseif($type == 'array')
	{
		return $tab;
	}

	return $options;

}

/**
 * Get nuts language by iso code
 *
 * @param string $initials
 * @return string language
 */
function nutsGetLanguage($initials)
{
	global $nuts_lang_options;

	foreach($nuts_lang_options as $n)
	{
		if($n['value'] == strtolower($initials))
		{
			return $n['label'];
		}
	}
}


/**
 * Get selected language by default
 *
 * @return string $language
 */
function nutsGetDefaultLanguage()
{
	$GLOBALS['nuts']->doQuery("SELECT
										LanguageDefault
							FROM
									NutsTemplateConfiguration");
	$lng = $GLOBALS['nuts']->getOne();
	return $lng;
}

/**
 * Get distinct zone defined by user
 *
 * @return array zone
 */
function nutsGetOptionsMenu()
{
	global $nuts_lang_msg;
	$options = '<option value="0">'.$nuts_lang_msg[41].'</option>'."\n";

	$GLOBALS['nuts']->doQuery("SELECT
										ID,
										Name
								FROM
										NutsZone
								WHERE
										Type = 'MENU' AND
										Deleted = 'NO'");
	while($row = $GLOBALS['nuts']->dbFetch())
		$options .= '<option value="'.$row['ID'].'">'.$row['Name'].'</option>'."\n";

	return $options;
}

/**
 * Get page tree for a specific zone
 *
 * @param string $Language
 * @param int $ZoneID (0 = main menu)
 * @param int $NutsPageID
 * @param string $State
 * @return string $html_ul
 */
function nutsGetMenu($Language='', $ZoneID = 0, $NutsPageID = 0, $State = '', $directID = '')
{
	global $nuts_lang_msg, $lang_msg, $plugin;

	$ul = '';

	// select direct by ID
	$directIDMode = false;
	$directID = (int)$directID;
	if($directID != 0)
	{
		$GLOBALS['nuts']->doQuery("SELECT ID, Language, ZoneID, State, AccessRestricted  FROM NutsPage WHERE Deleted = 'NO' AND ID = $directID");
		if($GLOBALS['nuts']->dbNumRows() == 0)
		{
			$msg = <<<EOF
			@NO_TREE@
			<script>alert("{$lang_msg[66]}");</script>
EOF;
			die(trim($msg));
		}

		// reselect & force currect zone, language, status
		$directIDMode = true;
		$row2 = $GLOBALS['nuts']->dbFetch();

		$ul .= '<script>';
		$ul .= '$("#Language").val("'.$row2['Language'].'");';
		$ul .= '$("#ZoneID").val("'.$row2['ZoneID'].'");';
		// $ul .= '$("#Status").val("'.$row2['State'].'");';
		$ul .= '</script>';

		$Language = $row2['Language'];
		$ZoneID = $row2['ZoneID'];
		$State = $row2['State'];
		$AccessRestricted = $row2['AccessRestricted'];

	}



	if(empty($Language))
	{
		$Language = nutsGetDefaultLanguage();
	}

	$root = $nuts_lang_msg[41];
	if($ZoneID != 0)
	{
		// get zone name
		$GLOBALS['nuts']->doQuery("SELECT Name FROM NutsZone WHERE ID = $ZoneID");
		$root = $GLOBALS['nuts']->getOne();
	}


	if($NutsPageID == 0)
	{
		$ul .= '<ul class="simpleTree">'."\n";
		$ul .= '<li class="root" id="0"><span><b>'.$root.'</b></span>';
		$ul .= "<ul>\n";
	}

	$sql_state = '';
	if($directIDMode)
	{
		$sql_state = "ID = '".$directID."' AND ";
	}
	else
	{
		if(!empty($State))
		{

			$sql_state .= "State = '".addslashes($State)."' AND ";

			/*$parents_page_possible_ID = nutsGetPageIDSRecursive($Language, $ZoneID, $NutsPageID, $State);

			if(empty($parents_page_possible_ID))
			{
				$sql_state .= "State = '".addslashes($State)."' AND ";
			}
			else
			{
				// send a request to found all page ID with state
				$sql_state .= "(State = '".addslashes($State)."' OR ";
				$sql_state .= "ID IN ($parents_page_possible_ID) ";
				$sql_state .= " ) AND ";
			}*/
		}
		else
		{
			$sql_state = " NutsPageID = $NutsPageID AND ";
		}
	}

	$GLOBALS['nuts']->doQuery("SELECT
										ID,
										MenuName,
										_HasChildren,
                                        State,
										AccessRestricted
								FROM
										NutsPage
								WHERE
										Language = '".addslashes($Language)."' AND
										ZoneID = $ZoneID AND
										$sql_state
										Deleted = 'NO'
								ORDER BY
										Position");

	while($row = $GLOBALS['nuts']->dbFetch())
	{
		$ul2 = '';
		if($row['_HasChildren'] == 'YES' && empty($State))
		{
			$ajax_url = "index.php?mod={$plugin->name}&do={$plugin->action}&_action=reload_page&ID={$row['ID']}";
			$ajax_url .= "&language={$Language}";
			$ajax_url .= "&zoneID={$ZoneID}";
			$ajax_url .= "&state={$State}";

			$ul2 = '<ul class="ajax">';
			$ul2 .= '	<li>{url:'.$ajax_url.'}</li>';
			$ul2 .= '</ul>';
		}

        $img = '';
        if($row['State'] == 'DRAFT')
            $img = "<img src='img/icon-tag-edit.png' align='absbottom' />";
        elseif($row['State'] == 'WAITING MODERATION')
            $img = "<img src='img/icon-tag-moderator.png' align='absbottom' />";

		$img_lock = '';
		 if($row['AccessRestricted'] == 'YES')
			$img_lock = "<img src='img/icon-lock.png' align='absbottom' /> ";


        $ul .= "\t".'<li id="'.$row['ID'].'"><span>'.$img_lock.$row['MenuName'].'</span>'.$img.$ul2.'</li>'."\n";
	}

	if($NutsPageID == 0)
	{
		$ul .= "</ul>\n";
		$ul .= '</ul>';
	}

	return $ul;
}

/**
 * Get count page for a specific zone
 *
 * @param string $Language
 * @param int $ZoneID
 * @param string $State
 * @return int counter
 */
function nutsGetCountPages($Language, $ZoneID, $State='')
{
	global $nuts;

	if(!empty($State))
	{
		$State = "State = '$State' AND";
	}

	$sql = "SELECT
					COUNT(*)
			FROM
					NutsPage
			WHERE
					Language = '$Language' AND
					ZoneID = $ZoneID AND
					$State
					Deleted = 'NO'";
	$nuts->doQuery($sql);

	$counter = (int) $nuts->getOne();
	return $counter;
}

/**
 * Create thumbnail image
 *
 * @param string $fname
 * @param int $thumbWidth max width to resize
 * @param bool $create_new create new one with $create_new_prefix and $create_new_suffix
 * @param int $create_new_height force height (by default 0 = generated)
 * @param string $create_new_prefix prefix for image (thumb_ by default)
 * @param string $create_new_suffix suffix for image (empty by default)
 * @return boolean
 */
function createThumb($fname, $thumbWidth, $create_new = false, $create_new_height = 0, $create_new_prefix = "thumb_", $create_new_suffix = "")
{
	$tmp = explode('/', $fname);
	$file = $tmp[count($tmp)-1];

	$info = pathinfo($fname);
    $ext = strtolower($info['extension']);

	if($ext == 'jpg' || $ext == 'jpeg')$img = imagecreatefromjpeg($fname);
	elseif($ext == 'png')$img = imagecreatefrompng($fname);
	elseif($ext == 'gif')$img = imagecreatefromgif($fname);

	$width = imagesx($img);
    $height = imagesy($img);

    // calculate thumbnail size
	$new_width = $thumbWidth;

	if(!$create_new_height)
		$new_height = floor($height * ($thumbWidth / $width));
	else
		$new_height = $create_new_height;

    $tmp_img = @imagecreatetruecolor($new_width, $new_height);
    //imagecopyresized( $tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
    imagecopyresampled( $tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

	// create new thumb
	if($create_new)
	{
		$fname = str_replace(basename($fname), $create_new_prefix.str_replace(".$ext", "", $file).$create_new_suffix.'.'.$ext, $fname);
	}

	if($ext == 'jpg' || $ext == 'jpeg')return imagejpeg($tmp_img, $fname, 100);
	elseif($ext == 'png')return imagepng($tmp_img, $fname);
	elseif($ext == 'gif')return imagegif($tmp_img, $fname);

	return false;
}

/**
 * Verify if is correct email
 *
 * @param string $email
 * @return boolean
 */
function email($email)
{
	$pattern = '#^[\w.-]+@[\w.-]+\.[a-zA-Z]{2,5}$#' ;
	return preg_match($pattern, $email);

}
/**
 * Send an email by nuts
 *
 * @param string $msg
 * @param array $data array foreach replacement
 * @param string $email mail address
 *
 * @return boolean success
 */
function nutsSendEmail($msg, $data, $email)
{
	global $nuts;

	$subject = '['.WEBSITE_NAME.'] '.$msg['subject'];
	$body = trim($msg['body']);

	$body = str_replace('{WEBSITE_NAME}', WEBSITE_NAME, $body);
	$body = str_replace('{WEBSITE_URL}', WEBSITE_URL, $body);

	foreach($data as $key => $val)
	{
		$body = str_replace('{'.$key.'}', $val, $body);
	}

	$body = rtrim($body);
	$body .= "

--
Powered by Nuts
User IP: ".$nuts->getIP();

	$headers = 'From: '.NUTS_EMAIL_NO_REPLY."\n";
	$headers .= "Content-Type: text/plain; charset=utf-8\n";
	// $headers .= 'To: '.$email."\n";

	// utf8_decode
	// $subject = utf8_decode($subject);
	// $body = utf8_decode($body);

	$subject = html_entity_decode($subject);
	if(!@mail($email, $subject, $body, $headers))
        return false;

    return true;

}

/**
 * Send Email throw Email module
 *
 * @param string $to seperated by comma
 * @param int $nutEmailID
 * @param array $datas
 *
 * @return boolean result
 */
function nutsMailer($to, $nutEmailID, $datas = array())
{
	global $nuts, $HTML_TEMPLATE;

	if(!isset($GLOBALS['nuts']) && isset($GLOBALS['job']) )
		$nuts = &$GLOBALS['job'];

	if(!isset($GLOBALS['NUTS_INCLUDES_EMAIL_CFG_VERIFY']))
	{
		include_once(WEBSITE_PATH."/plugins/_email/config.inc.php");
		$GLOBALS['NUTS_INCLUDES_EMAIL_CFG_VERIFY'] = true;
	}
	$GLOBALS['HTML_TEMPLATE'] = $HTML_TEMPLATE;

	$nutEmailID = (int)$nutEmailID;

	$nuts->doQuery("SELECT * FROM NutsEmail WHERE ID = $nutEmailID");
	if($nuts->dbNumRows() == 0)return false;
	$row = $nuts->dbFetch();

	// vars replacement
	$datas['WEBSITE_URL'] = WEBSITE_URL;
	$datas['WEBSITE_NAME'] = WEBSITE_NAME;

	foreach($datas as $key => $val)
	{
		$row['Subject'] = str_replace('{'.$key.'}', $val, $row['Subject']);
		$row['Body'] = str_replace('{'.$key.'}', $val, $row['Body']);
	}
	$row['Body'] = str_replace('[BODY]', $row['Body'], $HTML_TEMPLATE);

	$row['Body'] = str_replace('src="/', 'src="'.WEBSITE_URL.'/', $row['Body']);
	$row['Body'] = str_replace('href="/', 'href="'.WEBSITE_URL.'/', $row['Body']);


	// email send
	if(empty($row['Expeditor']))$row['Expeditor'] = NUTS_EMAIL_NO_REPLY;
	$nuts->mailFrom($row['Expeditor']);
	$nuts->mailCharset('utf-8');

	$row['Subject'] = html_entity_decode($row['Subject']);
	$nuts->mailSubject($row['Subject']);
	$nuts->mailBody($row['Body'], 'HTML');

	$to = explode(',', $to);

	$trt_ok = true;
	foreach($to as $t)
	{
		$t = strtolower(trim($t));
		if(!empty($t))
		{
			$nuts->mailTo($t);  // ajouté par JZ
			if(!$nuts->mailSend())
			{
				$trt_ok = false;
			}
		}
	}
	return $trt_ok;
}





/**
 * Convert the array into an array well structured
 *
 * @param array $arr
 *
 * @return array result formated
 */
function convertArrayForFormSelect($arr)
{
	$arrReturn   = array();
	foreach($arr as $key => $val)
	{
		$arrReturn[] = array('value' => $key, 'label' => $val);
	}
	return $arrReturn;
}



/**
 * Returns full name FirstName LastName of nuts user
 *
 * @param int ID optionnal = current user
 *
 * @return string
 */
function getNutsUserName($NutsUserID='')
{
	global $nuts;

	if(empty($NutsUserID))$NutsUserID = $_SESSION['NutsUserID'];

	$sql = "SELECT CONCAT(FirstName,' ', LastName) FROM NutsUser WHERE ID = $NutsUserID";
	$nuts->doQuery($sql);

	return $nuts->dbGetOne();

}

/**
 * Get an array with distinct list of email
 *
 * @param int $NutsGroupID
 * @param int $NutsUserID optionnal
 *
 * @return array
 */
function getNutsEmailList($NutsGroupID='', $NutsUserID='')
{
	global $nuts;


	$sql_added = '';
	if(!empty($NutsGroupID))
	{
		$sql_added .= "NutsGroupID = $NutsGroupID AND \n";
	}

	if(!empty($NutsUserID))
	{
		$sql_added .= "ID = $NutsUserID AND \n";
	}



	$sql = "SELECT
					DISTINCT Email
			FROM
					NutsUser
			WHERE
					$sql_added
					Deleted = 'NO'";

	$nuts->doQuery($sql);

	$arr = array();
	while($row = $nuts->dbFetch())
	{
		$arr[] = $row['Email'];
	}


	return $arr;
}

/**
 * Get nut page content
 *
 * @param int $pageID
 * @param array $fields
 *
 * @return array $res
 */
function nutsGetPage($pageID, $fields)
{
	global $nuts;

	$fields_str = join(',', $fields);
	$nuts->doQuery("SELECT $fields_str FROM NutsPage WHERE ID = $pageID");
	return $nuts->dbFetch();

}

/**
 * Convert a string to pascal case litteral (obsolete: use fromCamelCase instead)
 *
 * @param string string
 * @return string pascal case myCase => My case
 *
 */
function toPascalCase($str)
{
	$str = preg_replace('/(?<=[a-z])(?=[A-Z])/',' ', $str);

	$str = strtolower($str);
	$str = trim($str);
	$str = ucwords($str);

	return $str;
}



/**
 * Translates a camel case string into a string
 *
 * @param string $str String in camel case format
 * @param boolean $ucwords apply ucwords
 *
 * @return string $str Translated
 */
function fromCamelCase($str, $ucwords=true)
{
	$func = create_function('$c', 'return " " . $c[1];');
	$str = preg_replace_callback('/([A-Z])/', $func, $str);

	if($ucwords)$str = ucwords($str);

	return $str;
}

/**
 * Translates a string with underscores into camel case (e.g. first name -&gt; firstName)
 *
 * @param string $str String in underscore format
 * @param boolean $capitalize_first_char (If true (default), capitalise the first char in $str)
 *
 * @return string $str translated into camel caps
 */
function toCamelCase($str, $capitalize_first_char=true)
{
	if($capitalize_first_char)$str[0] = strtoupper($str[0]);

	$func = create_function('$c', 'return strtoupper($c[1]);');
	return preg_replace_callback('/ ([a-z])/', $func, $str);
}


/**
 * Convert a string to url rewrited
 *
 * @param string $str
 * @return string
 */
function strtouri($str)
{
	$str = mb_strtolower($str, 'utf8');

	$str = str_replace(array('à', 'â', 'ä'), 'a', $str);
	$str = str_replace(array('é', 'ê', 'è', 'ë'), 'e', $str);
	$str = str_replace(array('î', 'ï'), 'i', $str);
	$str = str_replace(array('ö', 'ô'), 'o', $str);
	$str = str_replace(array('ù', 'û', 'ü'), 'u', $str);
	$str = str_replace('ç', 'c', $str);
	$str = str_replace('"', '-', $str);
	$str = str_replace("'", '-', $str);
	$str = str_replace(' ', '-', $str);
	$str = str_replace('..', '.', $str);
	$str = str_replace('--', '-', $str);
	$str = str_replace('-.', '.', $str);

	return $str;
}



/**
 * Cut a string and concat (Warning UTF-8 uses mb_* function and strip_tags is applied before)
 *
 * @param type $str
 * @param type $max_caracters (default 80)
 * @param type $concat_str
 *
 * return string cutted string
 */
function str_cut($str, $max_caracters=80, $concat_str='...'){

	$str = strip_tags($str);
	$str2 = mb_strcut($str, 0, $max_caracters, 'UTF-8');
    $str2 = trim($str2);
	if(mb_strlen($str) > $max_caracters)
	{
		$str2 .= $concat_str;
	}

	return $str2;
}



/**
 * Is website is multilang configured
 * @return boolean
 */
function isWebsiteMultiLang()
{
	$GLOBALS['nuts']->doQuery("SELECT Languages FROM NutsTemplateConfiguration");
	$lng = $GLOBALS['nuts']->getOne();

	$lng = str_replace(' ', '', $lng);
	$lng = trim($lng);

	if(!empty($lng))
		return true;

	return false;

}

/**
 * Return formatted url for a nuts page
 *
 * @param int $ID
 * @param string $Language
 * @param string $VitualPageName
 * @param boolean $TagVersion true by defaults
 *
 * @return string
 */
function nutsGetPageUrl($ID, $Language, $virtualPagename, $TagVersion=true)
{
	// force direct url
	if(preg_match('/^http/i', $virtualPagename) || (!empty($virtualPagename) && $virtualPagename[0] == '/') || (!empty($virtualPagename) && $virtualPagename[0] == '{'))
	{
		return $virtualPagename;
	}

	if(!empty($virtualPagename))$virtualPagename = '-'.$virtualPagename;
	$url = "/$Language/{$ID}{$virtualPagename}.html";

	if($TagVersion)
	{
		$url = "{@NUTS	TYPE='PAGE'	CONTENT='URL'	ID='$ID'}";
	}

	return $url;
}


/**
 * A simple function using Curl to post (GET) to Twitter
 * Kosso : March 14 2007
 *
 * @param string $username
 * @param string $password
 * @param string $message
 * @return boolean
 */
function postToTwitter($username, $password, $message){

    // $host = "http://twitter.com/statuses/update.xml?status=".urlencode(stripslashes(urldecode($message)));
    $url = "http://twitter.com/statuses/update.xml";

	$curl_handle = curl_init();
	curl_setopt($curl_handle, CURLOPT_URL, $url);
	curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
	curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl_handle, CURLOPT_POST, 1);
	curl_setopt($curl_handle, CURLOPT_HTTPHEADER, array('Expect:'));
	curl_setopt($curl_handle, CURLOPT_POSTFIELDS, "status=$message");
	curl_setopt($curl_handle, CURLOPT_USERPWD, "$username:$password");
	$buffer = curl_exec($curl_handle);
	curl_close($curl_handle);

	// tweet no more supported
	//if(empty($buffer))
		$twitter_status = false;
	//else
		// $twitter_status = true;

    /*$ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_VERBOSE, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
    // curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, "status=$message");

    $result = curl_exec($ch);
    // Look at the returned header
    $resultArray = curl_getinfo($ch);
    curl_close($ch);

	new dBug($resultArray);

    if($resultArray['http_code'] == "200"){
         //$twitter_status = 'Your message has been sended! <a href="http://twitter.com/'.$username.'">See your profile</a>';
         $twitter_status = true;
    } else {
         //$twitter_status = "Error posting to Twitter. Retry";
         $twitter_status = false;
    }

	 */
	return $twitter_status;
}

/**
 * Transform an array to select
 *
 * @param array $options you can put multiple array for keys: label, value, optgroup
 * @param boolean $first_option_empty
 * @param string $select_name
 * @param string $attributes
 * @return string $select
 */
function array2select($options, $first_option_empty=true, $select_name="", $attributes=""){

	$select = "";
	$options_str = "";

	if($first_option_empty)
	{
		$options_str .= "<option value=\"\"></option>\n";
	}

	$last_optgroup = '';
	foreach($options as $option)
	{
		$selected = (!isset($option['selected'])) ? '' : 'selected="selected"';
		$value = $option['value'];
		$label = (!isset($option['label'])) ? $option['value'] : $option['label'];
		$optgroup = (!isset($option['optgroup'])) ? '' : $option['optgroup'];

		if(!empty($optgroup))
		{
			if($optgroup != $last_optgroup)
			{
				if(!empty($last_optgroup))
					$options_str .= "</optgroup>\n";

				$options_str .= "<optgroup label=\"$optgroup\">\n";
				$last_optgroup = $optgroup;
			}
		}

		$options_str .= "<option value=\"$value\" $selected>$label</option>\n";
	}

	if(!empty($last_optgroup))$options_str .= "</optgroup>\n";

	if(!empty($select_name))
	{
		if(!empty($attributes))$attributes .= ' '.$attributes;
		$select = "<select name=\"$select_name\" id=\"$select_name\"$attributes>\n";
		$select .= $options_str;
		$select .= "</select>\n";
	}
	else
	{
		$select = $options_str;
	}


	return $select;

}

/**
 * Convert an array to csv
 *
 * @param array $array your array
 * @param type $downloadable is file is for download ?
 * @param type $download_filename
 */
function array2csv($array, $downloadable=false, $download_filename='')
{
	$content = "";

	// lines
	$init = false;
	for($i=0; $i < count($array); $i++)
	{
		$line = $array[$i];

		if(!$init)
		{
			foreach($line as $key => $val)
			{
				$key = str_replace(';', ' ', $key);
				$content .= $key.';';
				$init = true;
			}

			$content .= CR;
		}

		foreach($line as $key => $val)
		{
			$val = str_replace(';', ',', $val);
			$val = str_replace(CR, '\n', $val);

			$content .= $val.';';
		}

		$content .= CR;
	}

	if(!$downloadable)
	{
		return $content;
	}
	else
	{
		if(empty($download_filename))
			$download_filename = date('Ymd').'.csv';

		// required for IE, otherwise Content-disposition is ignored
		if(@ini_get('zlib.output_compression'))@ini_set('zlib.output_compression', 'Off');

		header("Pragma: public"); // required
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private", false); // required for certain browsers
		header("Content-Type: application/force-download; charset=utf-8");
		header("Content-Disposition: attachment; filename=\"".basename($download_filename)."\";");
		header("Content-Transfer-Encoding: binary");

		echo $content;
		exit;

	}


}



/**
 * Convert array to html table
 *
 * @param array $rows
 * @param array $headers_labels (optional) replace text of a th text
 * @param string $headers_style (optional) add style to th and td, example text-align:center
 * @param string $table_attributes (optional) add attibutes in table node, example border="1"
 * @param string $td_colors1 (optional) change color for td even (default: #e5e5e5)
 * @param string $td_colors2 (optional) change color for td odd (default: #ffffff)
 *
 * @return string html table formatted
 */
function array2table($rows, $headers_labels=array(), $headers_style=array(), $table_attributes="", $table_styles="",  $td_colors1='#e5e5e5', $td_colors2='#ffffff')
{
	if(!count($rows))return "";


	$str = "<table $table_attributes style=\"$table_styles\">";

	$init = false;
	$i = 0;
	foreach($rows as $row)
	{
		if(!$init)
		{
			$headers = array_keys($row);

			$str .= '<tr>';
			foreach($headers as $header)
			{
				$header_label = $header;
				if(isset($headers_labels[$header]))
					$header_label = $headers_labels[$header];

				$str .= '	<th style="'.@$headers_style[$header].'">'.$header_label.'&nbsp;</th>';
			}

			$str .= '</tr>';

			$init = true;
		}

		$str .= '<tr>';
		$td_color = ($i % 2 == 0) ? $td_colors1 : $td_colors2;
		foreach($headers as $header)
		{
			$td_style = @$headers_style[$header];
			$td_style = "background-color: $td_color; $td_style";
			$str .= '	<td style="'.$td_style.'">'.$row[$header].'&nbsp;</td>';

		}
		$str .= '</tr>';

		$i++;
	}

	$str .= "</table>";


	return $str;
}




/**
 * Return image extension for a file
 *
 * @param string $file
 * @return string image
 */
function getImageExtension($file)
{
	$file_name = basename($file);
	$exts = explode('.', $file_name);
	$ext = strtolower(end($exts));
	$ext = trim($ext);

	$img = '<img src="/nuts/img/icon_extension/file.png" align="absmiddle" />';

	// doc
	if(in_array($ext, array('doc', 'docx', 'odt')))
	{
		$img = '<img src="/nuts/img/icon_extension/doc.png" align="absmiddle" />';
	}
	// excel
	elseif(in_array($ext, array('xls', 'xlsx', 'csv')))
	{
		$img = '<img src="/nuts/img/icon_extension/excel.png" align="absmiddle" />';
	}
	// pdf
	elseif(in_array($ext, array('pdf')))
	{
		$img = '<img src="/nuts/img/icon_extension/pdf.png" align="absmiddle" />';
	}
	// zip
	elseif(in_array($ext, array('zip')))
	{
		$img = '<img src="/nuts/img/icon_extension/zip.png" align="absmiddle" />';
	}
	// jpg, jpeg, gif, bmp, tiff, png, psd
	elseif(in_array($ext, array('jpg', 'jpeg', 'gif', 'png', 'bmp', 'tiff', 'psd')))
	{
		$img = '<img src="/nuts/img/icon_extension/image.png" align="absmiddle" />';
	}

	return $img;
}

/**
 * Format a float to be well displayed ex: 1234.5678 => 1 234.57
 * @param float $num
 * @return float
 */
function number_formatX($num)
{
	$num = number_format($num, 2, '.', ' ');


	return $num;
}


/**
 * Change contents for a special line for configuration file by example
 *
 * @param string $file
 * @param string $line_start
 * @param string $replacement
 * @return boolean
 */
function fileChangeLineContents($file, $line_start, $replacement)
{
	$file_contents = file_get_contents($file);
	if(!$file_contents)return false;

	$found = false;
	$lines = explode("\n", $file_contents);
	$i = 0;
	foreach($lines as $line)
	{
		$tmp_line = trim($line);
		if(strpos($tmp_line, $line_start) !== false && strpos($tmp_line, $line_start) == 0)
		{
			$lines[$i] = $replacement;
			$found = true;
			break;
		}
		$i++;
	}

	$new_file = join("\n", $lines);
	$new_file = trim($new_file);

	// save file
	if(!file_put_contents($file, $new_file))
		return false;

	return $found;

}

/**
 * get list fot tinymce spellchecker language
 */
function nutsGetSpellcheckerLanguages()
{
	global $nuts_lang_options;

    $str = '';
	$init = false;
	foreach($nuts_lang_options as $lng)
	{
		if(!empty($str))$str .= ',';

		$add = '';
		if(isset($_GET['lang']) && $_GET['lang'] == $lng['value'])
		{
			$add = '+';
			$init = true;
		}

		$str .= "$add{$lng['label']}={$lng['value']}";
	}

	if(!$init)
		$str = '+'.$str;


	return $str;
}




/**
 * Get all childrens for a page
 *
 * @param type $pageID
 * @return array with page ID inside structured
 */
function nutsPageGetChildrens($pageID, $init=false)
{
	global $nuts;

	if($init)
	{
		$IDs = array();
		$init = true;
	}

	$IDs[] = $pageID;
	$nuts->doQuery("SELECT ID FROM NutsPage WHERE NutsPageID = $pageID");
	$qID = $nuts->dbGetQueryID();
	while($pg = $nuts->dbFetch())
	{
		$pgs = nutsPageGetChildrens($pg['ID']);
		if(count($pgs) > 0)
			$IDs[] = $pgs;

		$nuts->dbSetQueryID($qID);
	}

	return $IDs;



}

/**
 * Flatten array
 *
 * @param array $a
 * @return array
 */
function array_flatten($array, $return=array())
{
	for($x = 0; $x <= count($array); $x++)
	{
		if(is_array(@$array[$x]))
		{
			$return = array_flatten($array[$x], $return);
		}
		else
		{
			if(@$array[$x])
			{
				$return[] = $array[$x];
			}
		}
	}
	return $return;
}


/**
 * Verify if user has right
 *
 * @param int $nutsUserID (empty = NutsGroupID in SESSION)
 * @return boolean
 */
function nutsUserHasRight($NutsGroupID='', $plugin, $right)
{
	global $nuts;

	$NutsGroupID = (int)$NutsGroupID;
    if(!$NutsGroupID)$NutsGroupID = $_SESSION['NutsGroupID'];


	$sql = "SELECT
					ID
			FROM
					NutsMenuRight
			WHERE
					NutsGroupID = $NutsGroupID AND
					Name = '$right' AND
					NutsMenuID IN(SELECT ID FROM NutsMenu WHERE Name = '$plugin')
			LIMIT
					1";
	$nuts->doQuery($sql);

	if(!(int)$nuts->dbNumRows())
		return false;



	return true;
}


/**
 * Generate thumbnail image dynamically from width and height attributes
 *
 * @param string $content
 * @return string content reformatted src image
 */
function smartImageResizer($content)
{
	$pattern = '/<img[^>]+>/i';
	preg_match_all($pattern, $content, $matches);

	$matches[0] = array_unique($matches[0]);

	foreach($matches[0] as $match)
	{
		if(strstr($match, 'src="/library/media/') !== false && strstr($match, 'height="') !== false && strstr($match, 'width="') !== false)
		{
			$tab = array();

			$tmp =  explode('src="', $match);
			$tmp =  explode('"', $tmp[1]);
			$tab['src'] = $tmp[0];

			$tmp =  explode('width="', $match);
			$tab['width'] = (int)$tmp[1];

			$tmp =  explode('height="', $match);
			$tab['height'] = (int)$tmp[1];

			// see original width and height
			list($original_width, $original_height, )  = @getimagesize(WEBSITE_PATH.$tab['src']);

			// see alreday defined
			$file_infos = @pathinfo($tab['src']);

			if($tab['width'] && $tab['width'] < $original_width && $tab['height'] && $tab['height'] < $original_height && isset($file_infos['filename']) && isset($file_infos['extension']) && in_array(strtolower($file_infos['extension']), array('jpg', 'png', 'gif')))
			{
				$tab['path'] = $file_infos['dirname'];
				$tab['file_name'] = $file_infos['basename'];
				$tab['extension'] = $file_infos['extension'];
				$tab['file_noext'] = $file_infos['filename'];
				$tab['file_thumbnail'] = $tab['file_noext']."-{$tab['width']}x{$tab['height']}.{$file_infos['extension']}";

				$thumb_full = WEBSITE_PATH.$tab['path'].'/'.$tab['file_thumbnail'];
				if(!file_exists($thumb_full))
				{
					if(createThumb(WEBSITE_PATH.$tab['src'], $tab['width'], true, $tab['height'], "", "-{$tab['width']}x{$tab['height']}"))
					{
						$imgX = $match;
						$imgX = str_replace("src=\"{$tab['src']}\"", "src=\"{$tab['path']}/{$tab['file_thumbnail']}\"", $imgX);
						$imgX = str_replace("width=\"{$tab['width']}\"", " ", $imgX);
						$imgX = str_replace("height=\"{$tab['height']}\"", " ", $imgX);

						$content = str_replace($match, $imgX, $content);
					}
				}
			}
		}
	}

	return $content;
}


/**
 * Log event in file
 *
 * @param string $msg
 * @param int $level
 * @param string $file if empty use trace.log
 */
function xLog($msg, $level=0, $file="")
{
	if(empty($file))
		$file = 'trace.log';

	$contents = @file_get_contents($file);
	if(!empty($contents))
		$contents .= "\n";

	$spaces = str_repeat("\t", $level);

	$contents .= "[".date('Y-m-d H:i:s')."]\t$spaces".ucfirst(trim($msg));
	file_put_contents($file, $contents);
}

/**
 * Trace application message in Log plugin
 *
 * @param string $app_name
 * @param string $message
 * @param int $recordID optionnal
 */
function xTrace($app_name, $message, $recordID=0)
{
	global $nuts;

	$qID = $nuts->dbGetQueryID();

	$f = array();
	$f['DateGMT'] = 'NOW()';
	$f['Application'] = 'jobs';
	$f['Action'] = $app_name;
	$f['Resume'] = $message;
	$f['IP'] = ip2long($nuts->getIp());

    if($recordID)
        $f['RecordID'] = $recordID;

	$nuts->dbInsert('NutsLog', $f);

	if($qID > -1)
		$nuts->dbSetQueryID($qID);
}

/**
 * Replace latin accent like éèêë by e for example
 *
 * @param $str
 * @return string
 */
function str_replace_latin_accents($str)
{

    $reps = array();
    $reps[] = array(
                        'pattern' => array('é', 'è', 'ê', 'ë'),
                        'replacement' => 'e'
                    );
    $reps[] = array(
                        'pattern' => array('à', 'â', 'ä', 'â'),
                        'replacement' => 'a'
                    );

    $reps[] = array(
                        'pattern' => array('ç'),
                        'replacement' => 'c'
    );


    $reps[] = array(
                        'pattern' => array('ÿ'),
                        'replacement' => 'y'
    );

    $reps[] = array(
                        'pattern' => array('û', 'ü', 'ù'),
                        'replacement' => 'u'
    );

    $reps[] = array(
                        'pattern' => array('î', 'ï'),
                        'replacement' => 'i'
    );

    $reps[] = array(
                        'pattern' => array('ö', 'ô'),
                        'replacement' => 'o'
    );


    foreach($reps as $rep)
    {
        $str = str_replace($rep['pattern'], $rep['replacement'], $str);

        $rep['pattern'] = array_map('strtoupper', $rep['pattern']);
        $str = str_replace($rep['pattern'], strtoupper($rep['replacement']), $str);
    }


    return $str;
}

/**
 * Transform Csv file to structured array
 *
 * @param $file_name
 * @param string $separator  (default = `;`)
 * @param bool $ignore_first_line  (default = true)
 * @param bool $first_line_as_key  (default = false)
 * @param bool $encode_utf8 (default = false)
 *
 * @return array
 */
function csv2array($file_name, $separator=';', $ignore_first_line=true, $first_line_as_key=false, $encode_utf8=false)
{
    $arr = array();
    $keys = array();

    $init = false;
    $lines = file($file_name);
    foreach($lines as $line)
    {
        $cols = explode($separator, $line);
        $cols = array_map('trim', $cols);
        if($encode_utf8)$cols = array_map('utf8_encode', $cols);
        if($ignore_first_line && !$init)
        {
            if($first_line_as_key)
            {
                $keys = array_map('toCamelCase', $cols);
                $keys = array_map('str_replace_latin_accents', $keys);
            }
        }

        if($init || !$ignore_first_line)
        {
            if($first_line_as_key)
            {
                $tmp = array();
                $i = 0;
                foreach($keys as $key)
                {
                    $tmp[$key] = $cols[$i];
                    $i++;
                }

                $arr[] = $tmp;
            }
            else
            {
                $arr[] = $cols;
            }


        }

        $init = true;
    }


    return $arr;
}


/**
 * Protect sql paramater against Xss attacks
 *
 * @param $str
 * @return string
 */
function sqlX($str)
{
    return strtr($str, array("\x00" => '\x00', "\n" => '\n', "\r" => '\r', '\\' => '\\\\', "'" => "\'", '"' => '\"', "\x1a" => '\x1a'));
}











?>