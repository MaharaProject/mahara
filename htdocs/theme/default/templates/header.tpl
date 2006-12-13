<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <title>{$title|default:"Mahara"|escape}</title>
        <script type="text/javascript">
        var config = {literal}{{/literal}
            'themeurl': '{$THEMEURL}'
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
        <link rel="stylesheet" type="text/css" href="{$THEMEURL}style/style.css">
        <link rel="stylesheet" type="text/css" href="{$THEMEURL}style/dev.css">
        <link rel="stylesheet" type="text/css" href="{$THEMEURL}style/print.css" media="print">
        <meta http-equiv="Content-type" content="text/html; charset=UTF-8">
    </head>
    <body>
	<div id="container">
		<div id="loading_box" style="display:none">
		</div>
		<div id="topwrapper">
{if $LOGGEDIN}
			<div id="globalTabs"><ul>
            	<li id="globalnav-logout"><a href="{$WWWROOT}?logout">Logout</a></li>
    {if $USER->get('admin')}
        {if $ADMIN}
            	<li id="globalnav-returntosite"><a href="{$WWWROOT}">Return to Site</a></li>
        {else}
            	<li id="globalnav-siteadmin"><a href="{$WWWROOT}admin/">Site Administration</a></li>
        {/if}
    {/if}
			</ul></div>
{/if}
			<div id="header">
				<div class="cornertopright"><img src="{image_path imagelocation='images/header_corner_topright.gif'}" border="0" alt=""></div>		
                {if !$nosearch}
				<div class="searchbox">
				<table cellpadding="0" cellspacing="0"><tr><td>
				{$searchform}
				</td></tr>
				<tr><td class="advancedsearch">
				<a href="{$WWWROOT}user/search.php">{str tag=advancedsearch}</a>
				</td></tr></table>
				</div>
                {/if}
				<div class="logo"><a href="{$WWWROOT}"><img src="{image_path imagelocation='images/logo.gif'}" border="0" alt=""></a></div>
				<h1 class="hiddenStructure"><a href="{$WWWROOT}">{$heading|default:"Mahara"|escape}</a></h1>
			</div>
		</div>
		<div id="mainwrapper">
{if $MAINNAV}
        <ul id="mainnav"><span class="mainnav-left"><img src="{image_path imagelocation='images/navbg_left.gif'}" border="0" alt=""></span><span class="mainnav-right"><img src="{image_path imagelocation='images/navbg_right.gif'}" border="0" alt=""></span>
{foreach from=$MAINNAV item=item}
    {if $item.selected}{assign var=MAINNAVSELECTED value=$item}
            <li class="selected"><a href="{$item.link|escape}">{str section=$item.section tag=$item.name}</a></li>
    {else}
            <li><a href="{$item.link|escape}">{str section=$item.section tag=$item.name}</a></li>
    {/if}
{/foreach}
        </ul>
    {if $MAINNAVSELECTED.submenu}
        <ul id="subnav"><span class="subnav-left"><img src="{image_path imagelocation='images/subnavbg_left_arrow.gif'}" border="0" alt=""></span><span class="subnav-right"><img src="{image_path imagelocation='images/subnavbg_right.gif'}" border="0" alt=""></span>
        {foreach from=$MAINNAVSELECTED.submenu item=item}
        {if $item.selected}{assign var=MAINNAVSELECTED value=$item}
            <li class="selected"><a href="{$item.link|escape}">{str section=$item.section tag=$item.name}</a></li>
        {else}
            <li><a href="{$item.link|escape}">{str section=$item.section tag=$item.name}</a></li>
        {/if}
        {/foreach}
        </ul>
    {/if}
{/if}	
        {insert name="messages"}
			<div id="maincontentwrapper">
		
	
