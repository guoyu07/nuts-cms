<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="robots" content="noindex, nofollow" />
	<title><?php echo strip_tags(urldecode($_GET['t'])); ?></title>
	<link rel="stylesheet" type="text/css" href="css/style.css" />
	<link rel="stylesheet" type="text/css" href="css/print.css?t=<?php echo time(); ?>" />
	<link rel="stylesheet" type="text/css" href="css/dialog_print.css?t=<?php echo time(); ?>" />

	<script type="text/javascript" src="../library/js/php.js"></script>

	<script type="text/javascript" src="../library/js/jquery.js"></script>
	<script src="../library/js/jquery.form.js"  type="text/javascript"></script>
	<script src="../library/js/jquery-ui/jquery-ui-personalized-1.5.1.js" type="text/javascript"></script>
	<link rel="stylesheet" href="../library/js/jquery-ui/themes/custom/jquery-ui-themeroller.css" />


</head>
<body>

	<script>
	function getIFrameDocument(aID){
		// if contentDocument exists, W3C compliant (Mozilla)
		if(document.getElementById(aID).contentWindow){
			return document.getElementById(aID).contentWindow.document;
		} else {
			// IE
			return document.frames[aID].document;
		}
	}

	$('body').append(window.opener.$("#form_content").html());	
	$('textarea.mceEditor').each(function(){

		id = $(this).attr('id');
		setTimeout( function() {
				getIFrameDocument('iframe_'+id).designMode = 'on';
				getIFrameDocument('iframe_'+id).body.innerHTML = $('textarea#'+id).val();

				head = getIFrameDocument('iframe_'+id).getElementsByTagName('head')[0];
				link = document.createElement('link');
				link.setAttribute('rel',"stylesheet");
				link.setAttribute('href',"/library/themes/editor_css.php?t=default");
				link.setAttribute('type',"text/css");
				head.appendChild(link);

		}, 200);
		
	});


	$(document).keyup(function(event){
		if(event.keyCode == 27)
			window.close();
    });

	count_time  = $('textarea.mceEditor').length * 3000;

	setTimeout( function() {window.print();}, count_time);
	</script>

</body>
</html>