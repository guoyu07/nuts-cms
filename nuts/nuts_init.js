setPluginTitle();
ajaxHistoricCheckChanges();

// POPUP MODE : escape to close
if(popupMode == 1)
{
    $('body').css('background-image', 'none');
    $('#add').css('margin-top', '-10px');

    parent_refresh = "parent_refresh";
    if(parent_refresh != "0" &&  strtoupper(parent_refresh) != "NO" && window.opener != null && window.opener != undefined && typeof window.opener.system_refresh == 'function')
    {
        window.onbeforeunload = function() {
            window.opener.force_ajax_async = false;
            window.opener.system_refresh();
        };
    }

    $(document).keydown(function(e){
        code = e.keyCode ? e.keyCode : e.which;
        if(code == 27)
        {
            if((c=confirm(nuts_lang_msg_62)))
            {
                if(typeof from_mode != undefined || from_mode != 'iframe')
                {
                    window.close();
                }
                else
                {
                    parent.$.fancybox.close();
                }
            }
        }
    });
}
else
{
    initMainMenu();
    initTopSearch();
    privateBoxRefresh();

    shortcut.add('Ctrl+Alt', function(){$('#bo_menu_switch a').click();});


    // add scroll event
    $(window).scroll(function(){

        if($('#header').length == 1)
        {
            wtp = $(window).scrollTop();
            if(wtp <= 130)
            {
                $('#header').css({top:'0px', 'border-bottom': '0', 'box-shadow': 'none'});
                // $('#header .user_login').show();
                $('#header .bo_logo').css({'margin-top':'5px'});
                $('#bo_menu_switch').css({'margin-top':'0px'});
                $('#top_search').css('top', '10px');
                $('#top_return').hide();
            }
            else
            {
                $('#header').css({top:wtp+'px', 'border-bottom': '1px solid #ccc', 'box-shadow': '0 0 15px #ccc'});
                // $('#header .user_login').hide();
                $('#bo_menu_switch').css({'margin-top':'-4px'});

                $('#header .bo_logo').css({'margin-top':'0px'});
                $('#top_search').css('top', (wtp+10)+'px');
                $('#top_return').show();
            }
        }
    });

}
