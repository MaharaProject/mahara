<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <title>{$title|default:"Mahara"|escape}</title>
        <script type="text/javascript">
        var config = {literal}{{/literal}
            'themeurl': '{$THEMEURL}',
            'wwwroot': '{$WWWROOT}'
        {literal}}{/literal};
        </script>
{foreach from=$JAVASCRIPT item=script}        <script type="text/javascript" src="{$script}"></script>
{/foreach}
{foreach from=$HEADERS item=header}        {$header}
{/foreach}
{if isset($INLINEJAVASCRIPT)}
        <script type="text/javascript">
{$INLINEJAVASCRIPT}
        </script>
{/if}
        <script type="text/javascript" src="/js/pieforms.js"></script>
        <link rel="stylesheet" type="text/css" href="{$THEMEURL}style/style.css">
        <link rel="stylesheet" type="text/css" href="{$THEMEURL}style/dev.css">
        <link rel="stylesheet" type="text/css" href="{$THEMEURL}style/print.css" media="print">
        <meta http-equiv="Content-type" content="text/html; charset=UTF-8">
    </head>
    <body>
	<div id="container">
        <div id="loading_box" style="display: none;"></div>
        <div id="topwrapper">
{if $LOGGEDIN}
            <div id="globalTabs">
                <ul>
                    <li id="globalnav-logout"><a href="{$WWWROOT}?logout">Logout</a></li>
{if $USER->get('admin')}
{if $ADMIN}
                    <li id="globalnav-returntosite"><a href="{$WWWROOT}">Return to Site</a></li>
{else}
                    <li id="globalnav-siteadmin"><a href="{$WWWROOT}admin/">Site Administration</a></li>
{/if}
                    {* <li><a href="" onclick="createLoggingPane(); return false;">Create Logging Pane</a></li> *}
{/if}
                </ul>
            </div>
{/if}
            <div id="header">
                <div class="fr"><img src="{image_path imagelocation='images/header_corner_topright.gif'}" border="0" alt=""></div>		

                <table cellspacing="0" class="searchbox fr">
{if !$nosearch}
                    <tr>
                        <td>{$searchform}</td>
                    </tr>
                    <tr>
                        <td class="advancedsearch"><a href="{$WWWROOT}user/search.php">{str tag=advancedsearch}</a></td>
                    </tr>
{/if}
					<tr>
						<td><div id="loggedinstring">{$LOGGEDINSTR}</div></td>
					</tr>	
                </table>

                
                <div id="logo"><a href="{$WWWROOT}"><img src="{image_path imagelocation='images/logo.gif'}" border="0" alt=""></a></div>
                <h1 class="hiddenStructure"><a href="{$WWWROOT}">{$heading|default:"Mahara"|escape}</a></h1>
            </div>
        </div>
        <div id="mainwrapper">
{if $MAINNAV}
            
            <ul id="mainnav"><div class="mainnavright"><img src="{image_path imagelocation='images/navbg_right.gif'}" border="0" alt=""></div>
{foreach from=$MAINNAV item=item}
{if $item.selected}{assign var=MAINNAVSELECTED value=$item}
                <li class="selected"><a href="{$item.link|escape}">{str section=$item.section tag=$item.name}</a></li>
{else}
                <li><a href="{$item.link|escape}">{str section=$item.section tag=$item.name}</a></li>
{/if}
{/foreach}
            </ul>
{if $MAINNAVSELECTED.submenu}
            
            <ul id="subnav"><div class="subnavright"><img src="{image_path imagelocation='images/subnavbg_right.gif'}" border="0" alt=""></div>
{foreach from=$MAINNAVSELECTED.submenu item=item}
                <li{if $item.selected} class="selected"{/if}><a href="{$item.link|escape}">{str section=$item.section tag=$item.name}</a></li>
{/foreach}
            </ul>
{/if}
{/if}	
            {insert name="messages"}
            <div id="maincontentwrapper">
