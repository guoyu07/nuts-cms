<?php
/*
PDW File Browser v1.3 beta
Date: October 19, 2010
Url: http://www.neele.name

Copyright (c) 2010 Guido Neele

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/

// hack for nuts ***********************************
$str = explode('library', $_SERVER['SCRIPT_FILENAME']);
include_once($str[0].'nuts/config.inc.php');
include_once(WEBSITE_PATH.'/nuts/config_auto.inc.php');
//**************************************************


if(!isset($_SESSION)){ session_start();}

/*
 * Uncomment lines below to enable PHP error reporting and displaying PHP errors.
 * Do not do this on a production server. Might be helpful when debugging why PDW File Browser
 * does not work as expected.
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('memory_limit', '128M');

/**
 * WARNING: You should do your authorization checking right here. config.php is included
 * in every file so checking it here is securing the whole plug-in. By not checking for
 * authorization you are allowing "anyone" to upload and list the files in your server.
 * You must implement some kind of session validation here. You could do something like...
 *
 * if (!(isset($_SESSION['IsAuthorized']) && $_SESSION['IsAuthorized'])){
 *      die("You are not authorized!");
 * }
 *
 * ... where $_SESSION['IsAuthorized'] is set to "true" as soon as the user logs in to your system.
**/
if(@!$_SESSION['NutsUserID'])
{
	die("Error: you are not allowed to access");
}


/*
 * UPLOAD PATH
 *
 * absolute path from root to upload folder (DON'T FORGET SLASHES)
 *
 * Example
 * ---------------------------------------
 * http://www.domain.com/images/upload/
 * $uploadpath = '/images/upload/';
 *
 */
// $uploadpath = "/path/to/upload/folder/"; // absolute path from root to upload folder (DON'T FORGET SLASHES)
$uploadpath = "/library/media/"; // absolute path from root to upload folder (DON'T FORGET SLASHES)
if(!isset($_SESSION['PdwBrowserLastTypeViewed']))$_SESSION['PdwBrowserLastTypeViewed'] = '';

if(@$_GET['filter'] == 'image' || (!isset($_GET['filter']) && @$_SESSION['PdwBrowserLastTypeViewed'] == 'image'))
{
	if(!isset($_GET['filter']))$_GET['filter'] = $_SESSION['PdwBrowserLastTypeViewed'];

	$uploadpath = "/library/media/images/user/";
	if(!@empty($_GET['path']))
	{
		$path = $_GET['path'];
		if($path[strlen($path)-1] == '/')$path[strlen($path)-1] = '';
		$uploadpath .= $path.'/';
	}


	$_SESSION['PdwBrowserLastTypeViewed'] = $_GET['filter'];
}

if(@$_GET['filter'] == 'media' || (!isset($_GET['filter']) && @$_SESSION['PdwBrowserLastTypeViewed'] == 'media'))
{
	if(!isset($_GET['filter']))$_GET['filter'] = $_SESSION['PdwBrowserLastTypeViewed'];

	$uploadpath = "/library/media/multimedia/";
	if(!@empty($_GET['path']))
	{
		$path = $_GET['path'];
		if($path[strlen($path)-1] == '/')$path[strlen($path)-1] = '';
		$uploadpath .= $path.'/';
	}

	$_SESSION['PdwBrowserLastTypeViewed'] = $_GET['filter'];
}

if(@$_GET['filter'] == 'file' || (!isset($_GET['filter']) && @$_SESSION['PdwBrowserLastTypeViewed'] == 'file'))
{
	if(!isset($_GET['filter']))$_GET['filter'] = $_SESSION['PdwBrowserLastTypeViewed'];

	$uploadpath = "/library/media/other/";
	if(!@empty($_GET['path']))
	{
		$path = $_GET['path'];
		if($path[strlen($path)-1] == '/')$path[strlen($path)-1] = '';
		$uploadpath .= $path.'/';
	}

	$_SESSION['PdwBrowserLastTypeViewed'] = $_GET['filter'];
}

// dynamic check upload path
$tmp_uploadpath = $uploadpath;
$tmp_uploadpath[strlen($tmp_uploadpath)-1] = '';
if(!empty($_SESSION['PdwBrowserLastTypeViewed']) && !is_dir(WEBSITE_PATH.$tmp_uploadpath))
{
	@mkdir(WEBSITE_PATH.$tmp_uploadpath, 0755, true);
}


/*
 * DEFAULT TIMEZONE
 *
 * If you use PHP 5 then set default timezone to avoid any date errors.
 *
 * Select the timezone you are in.
 *
 * Timezones to select from are http://nl3.php.net/manual/en/timezones.php
 *
 */
//date_default_timezone_set('Europe/Amsterdam');

/*
 * VIEW LAYOUT
 *
 * Set the default view layout when the file browser is first loaded
 *
 * Your options are: 'large_images', 'small_images', 'list', 'content', 'tiles' and 'details'
 *
 */
$viewLayout = 'small_images';

/*
 * DEFAULT LANGUAGE
 *
 * Set default language to load when &language=? is not included in url
 *
 * See lang directory for included languages. For now your options are 'en' and 'nl'
 * But you are free to translate the language files in the /lang/ directory. Copy the
 * en.php file and translate the lines after the =>
 *
 */
$defaultLanguage = $_SESSION['Language'];

/*
 * ALLOWED ACTIONS
 *
 * Set an action to FALSE to prevent execution.
 * Buttons will be removed from UI when an action is set to FALSE.
 *
 */
$allowedActions = array(
    'upload' => ($_SESSION['AllowUpload'] == 'YES') ? true : false,
	'settings' => false,
    'cut_paste' => ($_SESSION['AllowEdit'] == 'YES') ? true : false,
	'copy_paste' => ($_SESSION['AllowEdit'] == 'YES') ? true : false,
	'rename' => ($_SESSION['AllowEdit'] == 'YES') ? true : false,
	'delete' => ($_SESSION['AllowDelete'] == 'YES') ? true : false,
	'create_folder' => ($_SESSION['AllowFolders'] == 'YES') ? true : false
);

/*
 * PDW File Browser depends on $_SERVER['DOCUMENT_ROOT'] to resolve path/filenames. This value is usually
 * correct, but has been known to be broken on some servers. This value allows you to override the default
 * value.
 * Do not modify from the auto-detect default value unless you are having problems.
 */
//define('DOCUMENTROOT', '/home/httpd/httpdocs');
//define('DOCUMENTROOT', 'c:\\webroot\\example.com\\www');
//define('DOCUMENTROOT', $_SERVER['DOCUMENT_ROOT']);
//define('DOCUMENTROOT', realpath((@$_SERVER['DOCUMENT_ROOT'] && file_exists(@$_SERVER['DOCUMENT_ROOT'].$_SERVER['PHP_SELF'])) ? $_SERVER['DOCUMENT_ROOT'] : str_replace(dirname(@$_SERVER['PHP_SELF']), '', str_replace(DIRECTORY_SEPARATOR, '/', realpath('.')))));
// define('DOCUMENTROOT', realpath((getenv('DOCUMENT_ROOT') && preg_match('#^'.preg_quote(realpath(getenv('DOCUMENT_ROOT'))).'#', realpath(__FILE__))) ? getenv('DOCUMENT_ROOT') : str_replace(dirname(@$_SERVER['PHP_SELF']), '', str_replace(DIRECTORY_SEPARATOR, '/', dirname(__FILE__)))));
define('DOCUMENTROOT', WEBSITE_PATH);

/*
 * CUSTOM FILTERS
 *
 * If you like to use custom filters then remove "//" to add your own filters.
 * "name of filter" => ".extension1|.extension2"
 */
$customFilters = array(
    "MS Office" => ".doc|.docx|.xsl|.xlsx|.ppt|.pptx",
    "PDF" => ".pdf"
);

/*
 * DEFAULT SKIN
 *
 * Take a look inside the /skin/ folder to see which skins are available. If you leave the "//"
 * then redmond (Windows 7 like) will be the default theme.
 */
$defaultSkin = "mountainview";



/*
 * EDITOR
 *
 * Which editor are we dealing with? PDW File Browser can be used with TinyMCE and CKEditor.
 */
$editor = isset($_GET["editor"]) ? $_GET["editor"] : ''; // If you want to use the file browser for both editors and/or standalone
//$editor="tinymce";
//$editor="ckeditor";
//$editor="standalone";


/*
 * UPLOAD SETTINGS
 *
 */
// Maximum file size
// $max_file_size_in_bytes = 1048576; // 1MB in bytes

$max_file_size = ini_get('upload_max_filesize');

$max_file_size_in_bytes = $max_file_size; // 1MB in bytes
$max_file_size_in_bytes = (int)str_replace('M', "", $max_file_size_in_bytes);
$max_file_size_in_bytes *= 1024 * 1024;




// Characters allowed in the file name (in a Regular Expression format)
$valid_chars_regex = '.A-Za-z0-9_ !@%()+=\[\]\',~`-';

// Allowed file extensions
// Remove an extension if you don't want to allow those files to be uploaded.
//$extension_whitelist = "7z,aiff,asf,avi,bmp,csv,doc,docx,fla,flv,gif,gz,gzip,jpeg,jpg,mid,mov,mp3,mp4,mpc,mpeg,mpg,ods,odt,pdf,png,ppt,pptx,pxd,qt,ram,rar,rm,rmi,rmvb,rtf,sdc,sitd,swf,sxc,sxw,tar,tgz,tif,tiff,txt,vsd,wav,wma,wmv,xls,xlsx,zip";
/*$extension_whitelist = "jpg,gif,png"; // Images
$extension_whitelist .= ",mp3,ogg"; // Audio
$extension_whitelist .= ",mp4,flv,swf,srt"; // Video
$extension_whitelist .= ",pdf,zip,doc,docx,xls,xlsx,ppt,pptx"; // Others
*/

$filetypes = array();

// images
$filetypes['jpg'] = array('image/jpeg', 'image/pjpeg');
$filetypes['gif'] = array('image/gif');
$filetypes['png'] = array('image/png');

// audio
$filetypes['mp3'] = array('audio/mpeg', 'audio/mpeg3', 'audio/x-mpeg-3', 'video/mpeg', 'video/x-mpeg');

// video
$filetypes['mp4'] = array('audio/mp4');
$filetypes['swf'] = array('application/x-shockwave-flash');
$filetypes['flv'] = array('video/x-flv', 'video/flv');

// other
$filetypes['pdf'] = array('application/pdf','application/x-pdf', 'application/octet-stream');
$filetypes['zip'] = array('application/x-compressed', 'application/zip', 'application/octet-stream', 'application/x-zip-compressed');

$filetypes['doc'] = array('application/msword');
$filetypes['docx'] = array('application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

$filetypes['xls'] = array('application/x-excel', 'application/vnd.ms-excel');
$filetypes['xlsx'] = array('application/x-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

$filetypes['ppt'] = array('application/powerpoint', 'application/mspowerpoint', 'application/powerpoint', 'application/vnd.ms-powerpoint', 'application/x-mspowerpoint', 'application/mspowerpoint');
$filetypes['pptx'] = array('application/powerpoint', 'application/mspowerpoint', 'application/powerpoint', 'application/vnd.ms-powerpoint', 'application/x-mspowerpoint', 'application/mspowerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation');








/*
 * RETURN LINKS AS ABSOLUTE OR ABSOLUTE WITHOUT HOSTNAME
 *
 * Ex. http://www.example.com/upload/file.jpg instead of /upload/file.jpg
 */
$absolute_url = false; // When FALSE url will be returned absolute without hostname, like /upload/file.jpg.
$absolute_url_disabled = false; // When TRUE changing from absolute to relative is not possible.







//--------------------------DON'T EDIT BEYOND THIS LINE ----------------------------------

$filetypes_exts = array();
$filetypes_mimes = array();
foreach($filetypes as $filetype => $mimes)
{
	$filetypes_exts[] = $filetype;
	$filetypes_mimes = array_merge($filetypes_mimes, $mimes);
}



define('STARTINGPATH', DOCUMENTROOT . $uploadpath); //DON'T EDIT

//Check if upload folder exists
if(!@is_dir(STARTINGPATH)) die("Error: folder `$uploadpath` doesn't exist");

//Check if editor is set
if(!isset($editor)) die('The variable $editor in config.php is not set!');

// Figure out which language file to load
if(!empty($_REQUEST['language'])) {
	$language = $_REQUEST['language'];
} elseif (isset($_SESSION['language'])) {
	$language = $_SESSION['language'];
} else {
	$language = $defaultLanguage;
}

require_once("lang/".$language.".php");
$_SESSION['language'] = $language;

// Get local settings from language file
$datetimeFormat = $lang["datetime format"];				// 24 hours, AM/PM, etc...
$dec_seperator = $lang["decimal seperator"]; 			// character in front of the decimals
$thousands_separator = $lang["thousands separator"];	// character between every group of thousands


// Check post_max_size (http://us3.php.net/manual/en/features.file-upload.php#73762)
function let_to_num($v){ //This function transforms the php.ini notation for numbers (like '2M') to an integer (2*1024*1024 in this case)
    $l = substr($v, -1);
    $ret = substr($v, 0, -1);
    switch(strtoupper($l)){
        case 'P': $ret *= 1024;
        case 'T': $ret *= 1024;
        case 'G': $ret *= 1024;
        case 'M': $ret *= 1024;
        case 'K': $ret *= 1024;
        break;
    }
    return $ret;
}

$max_upload_size = min(let_to_num(ini_get('post_max_size')), let_to_num(ini_get('upload_max_filesize')));

if ($max_file_size_in_bytes > $max_upload_size) {
    $max_file_size_in_bytes = $max_upload_size;
}
?>