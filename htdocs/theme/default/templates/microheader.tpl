<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <title>{$PAGETITLE|escape}</title>
        <script type="text/javascript">
        var config = {literal}{{/literal}
            'theme': {$THEMELIST},
            'sesskey' : '{$SESSKEY}',
            'wwwroot': '{$WWWROOT}',
            'loggedin': {$USER->is_logged_in()|intval}
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
        <script type="text/javascript" src="{$WWWROOT}js/pieforms.js"></script>
{foreach from=$STYLESHEETLIST item=cssurl}
        <link rel="stylesheet" type="text/css" href="{$cssurl}">
{/foreach}
        <link rel="stylesheet" type="text/css" href="{theme_path location='style/dev.css'}">
        <link rel="stylesheet" type="text/css" href="{theme_path location='style/print.css'}" media="print">
        <meta http-equiv="Content-type" content="text/html; charset=UTF-8">
        <style type="text/css">
            {literal}
            body { background: white; }
            .micro_footer { position: fixed; bottom: 3px; }
            {/literal}
        </style>
    </head>
    <body>
    <div id="containerX">
        <div id="loading_box" style="display: none;"></div>
        <div id="topwrapperX">

            <div id="header">
                <div class="frX"></div>
                <div id="logo"><a href="{$WWWROOT}"><img src="{theme_path location='images/logo_mahara.gif'}" border="0" alt=""></a></div>
                <h1 class="hiddenStructure"><a href="{$WWWROOT}">{$heading|default:"Mahara"|escape}</a></h1>
            </div>
        </div>
        <div id="mainwrapperX">
            {insert name="messages"}
            <div id="maincontentwrapper">
            <div class="maincontent">
                {if $PAGEHELPNAME} <div id="{$PAGEHELPNAME}_container" class="pagehelpicon">{$PAGEHELPICON}</div>{/if}