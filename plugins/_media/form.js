/*for(i=0; i < hfs.length; i++)
{
	n = trim(hfs[i]);
	
	if(!empty(n))
	{
		if(n.indexOf('fieldset') == 0)
			$('#former #'+n).hide();
		else
			$('#former #'+n).parent('p').hide();
	}
}


// hide filters
if($('#former #fieldset_CustomFilter').children('p').length == 0)
	$('#former #fieldset_CustomFilter').hide();
*/

$('#former #Type').change(function(){

	if($(this).val() == 'AUDIO')
	{
		$('#former #Url').parents('p').show();
		$('#former #fieldset_VideoParams').hide();
		$('#former #fieldset_AudioParams').show();
		$('#former #EmbedCode').parents('p').hide();
		$('#former #EmbedCodePreviewUrl').parents('p').hide();
	}
	else if($(this).val() == 'VIDEO')
	{
		$('#former #Url').parents('p').show();
		$('#former #fieldset_VideoParams').show();
		$('#former #fieldset_AudioParams').hide();
		$('#former #EmbedCode').parents('p').hide();
		$('#former #EmbedCodePreviewUrl').parents('p').hide();
	}
	else if($(this).val() == 'EMBED CODE')
	{
		$('#former #fieldset_VideoParams').hide();
		$('#former #fieldset_AudioParams').hide();
		$('#former #Url').parents('p').hide();
		$('#former #EmbedCode').parents('p').show();
		$('#former #EmbedCodePreviewUrl').parents('p').show();
	}

});

$('#former #Type').change();

// update from parameters
params = $('#former #Parameters').val();
if(!empty(params))
{
	tabs = explode('@@', params);
	for(i=0; i < tabs.length; i++)
	{
		if(!empty(tabs[i]))
		{
			v2 = explode('=>', tabs[i]);

			prefix = 'PA_';
			if($('#former #Type').val() == 'VIDEO')
				prefix = 'PV_';
			
			if(!empty(v2[0]))
				$('#former #'+prefix+v2[0]).val(v2[1]);

		}
	}
}


