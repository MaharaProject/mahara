{auto_escape off}
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-type" content="text/html; charset=UTF-8">
	<title>{$PAGETITLE|escape}</title>
	<script type="text/javascript">
	var config = {literal}{{/literal}
		'theme': {$THEMELIST},
		'sesskey' : '{$SESSKEY}',
		'wwwroot': '{$WWWROOT}',
		'loggedin': {$USER->is_logged_in()|intval},
		'userid': {$USER->get('id')}
	{literal}}{/literal};
	</script>
	{$STRINGJS}
{foreach from=$JAVASCRIPT item=script}
	<script type="text/javascript" src="{$script}"></script>
{/foreach}
{foreach from=$HEADERS item=header}
	{$header}
{/foreach}
{if isset($INLINEJAVASCRIPT)}
	<script type="text/javascript">
{$INLINEJAVASCRIPT}
	</script>
{/if}
{foreach from=$STYLESHEETLIST item=cssurl}
	<link rel="stylesheet" type="text/css" href="{$cssurl}">
{/foreach}
	<link rel="stylesheet" type="text/css" href="{theme_path location='style/print.css'}" media="print">
    <link rel="shortcut icon" href="{$WWWROOT}favicon.ico" type="image/vnd.microsoft.icon">
</head>
<body>
{if $USERMASQUERADING}<div class="sitemessage"><img src="{theme_path location='images/icon_problem.gif'}" alt="">{$masqueradedetails} {$becomeyouagain}</div>{/if}
{if $SITECLOSED}<div class="sitemessage center">{$SITECLOSED}</div>{/if}
<div id="container">
	<div id="loading_box" class="hidden"></div>
	<div id="topwrapper">
		<table cellspacing="0" class="searchbox fr">
{if !$nosearch && $LOGGEDIN}
		<tr>
			<td>{$searchform}</td>
		</tr>
{/if}
{if defined('MENUITEM') && MENUITEM == '' && !$LOGGEDIN && (count($LANGUAGES) > 1)}
		<tr class="headerlanguage">
			<td>
			<form method="post">
			<label>{str tag=language}: </label>
			<select name="lang">
			<option value="default" selected="selected">{$sitedefaultlang}</option>
{foreach from=$LANGUAGES key=k item=i}
			<option value="{$k|escape}">{$i|escape}</option>
{/foreach}
			</select>
			<input type="submit" class="submit" name="changelang" value="{str tag=change}" />
			</form>
			</td>
		</tr>
{/if}
		</table>
		<div id="logo"><a href="{$WWWROOT}"><img src="{theme_path location='images/logo.gif'}" border="0" alt=""></a></div>
		<h1 class="hidden"><a href="{$WWWROOT}">{$hiddenheading|default:"Mahara"|escape}</a></h1>
{if $MAINNAV}
		<div id="mainnav">
			<ul>
{foreach from=$MAINNAV item=item}
{if $item.selected}{assign var=MAINNAVSELECTED value=$item}<li class="selected"><a href="{$WWWROOT}{$item.url|escape}">{$item.title|escape}</a></li>{else}<li><a href="{$WWWROOT}{$item.url|escape}">{$item.title|escape}</a></li>{/if}{/foreach}{if $LOGGEDIN}{if $USER->get('admin') || $USER->is_institutional_admin()}{if $ADMIN || $INSTITUTIONALADMIN}<li><a href="{$WWWROOT}">{str tag="returntosite"}</a></li>{elseif $USER->get('admin')}<li><a href="{$WWWROOT}admin/">{str tag="siteadministration"}</a></li>{else}<li><a href="{$WWWROOT}admin/users/search.php">{str tag="useradministration"}</a></li>{/if}{* <li><a href="" onclick="createLoggingPane(); return false;">Create Logging Pane</a></li> *}{/if}<li><a href="{$WWWROOT}?logout">{str tag="logout"}</a></li>{/if}
			</ul>
		</div>
	</div>
	<div id="subnav">
{if $MAINNAVSELECTED.submenu}
	<ul>
{foreach from=$MAINNAVSELECTED.submenu item=item}<li{if $item.selected} class="selected"{/if}><a href="{$WWWROOT}{$item.url|escape}">{$item.title|escape}</a></li>{/foreach}
	</ul>
{/if}
{/if}
	</div>
	<div id="mainwrapper">{/auto_escape}
