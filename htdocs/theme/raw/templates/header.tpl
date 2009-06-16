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
    <link rel="stylesheet" type="text/css" href="{theme_url filename='style/print.css'}" media="print">
    <script type="text/javascript" src="{$WWWROOT}js/css.js"></script>
    <link rel="shortcut icon" href="{$WWWROOT}favicon.ico" type="image/vnd.microsoft.icon">
</head>
<body>
{if $USERMASQUERADING}<div class="sitemessage"><img src="{theme_url filename='images/icon_problem.gif'}" alt="">{$masqueradedetails} {$becomeyouagain}</div>{/if}
{if $SITECLOSED}<div class="sitemessage center">{$SITECLOSED}</div>{/if}
<div id="container">
    <div id="loading-box"></div>
    <div id="top-wrapper">
        <h1 id="site-logo"><a href="{$WWWROOT}"><img src="{theme_url filename='images/site-logo.png'}" alt="{$sitename|escape}"></a></h1>
{if !$nosearch && $LOGGEDIN}        {user_search_form}{/if}
{if !$nosearch && !$LOGGEDIN && (count($LANGUAGES) > 1)}
        <form id="language-select" method="post" action="">
            <div>
                <label>{str tag=language}: </label>
                <select name="lang">
                    <option value="default" selected="selected">{$sitedefaultlang}</option>
{foreach from=$LANGUAGES key=k item=i}
                    <option value="{$k|escape}">{$i|escape}</option>
{/foreach}
                </select>
                <input type="submit" class="submit" name="changelang" value="{str tag=change}">
            </div>
        </form>
{/if}
{if $MAINNAV}
        <div id="main-nav">
            <ul>{strip}
{foreach from=$MAINNAV item=item}
                <li{if $item.selected}{assign var=MAINNAVSELECTED value=$item} class="selected"{/if}><a href="{$WWWROOT}{$item.url|escape}">{$item.title|escape}</a></li>
{/foreach}
{if $LOGGEDIN}{if $USER->get('admin') || $USER->is_institutional_admin()}
{if $ADMIN || $INSTITUTIONALADMIN}
                <li><a href="{$WWWROOT}">{str tag="returntosite"}</a></li>
{elseif $USER->get('admin')}
                <li><a href="{$WWWROOT}admin/">{str tag="siteadministration"}</a></li>
{else}
                <li><a href="{$WWWROOT}admin/users/search.php">{str tag="useradministration"}</a></li>
{/if}
{/if}
                <li><a href="{$WWWROOT}?logout">{str tag="logout"}</a></li>
{/if}
            {/strip}</ul>
        </div>
        <div id="sub-nav">
{if $MAINNAVSELECTED.submenu}
            <ul>{strip}
{foreach from=$MAINNAVSELECTED.submenu item=item}
                <li{if $item.selected} class="selected"{/if}><a href="{$WWWROOT}{$item.url|escape}">{$item.title|escape}</a></li>
{/foreach}
            {/strip}</ul>
{/if}
        </div>
{/if}
    </div>
    <table id="main-wrapper">
        <tbody>
            <tr>
{if $SIDEBARS && $SIDEBLOCKS.left}
                <td id="left-column" class="sidebar"{if $THEME->leftcolumncss} style="{$THEME->leftcolumncss|escape}"{/if}>
{include file="sidebar.tpl" blocks=$SIDEBLOCKS.left}
                </td>
{/if}
                <td id="main-column"{if $THEME->maincolumncss} style="{$THEME->maincolumncss|escape}"{/if}>
                    {insert name="messages"}
                    <div id="main-column-container">

{if $PAGEHEADING}                    <h1>{$PAGEHEADING}{if $PAGEHELPNAME}<span class="page-help-icon">{$PAGEHELPICON}</span>{/if}</h1>
{/if}
