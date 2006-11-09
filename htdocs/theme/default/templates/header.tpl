<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <title>{$title|default:"Mahara"|escape}</title>
        {strip}
            {foreach from=$JAVASCRIPT item=script}
                <script language="javascript" type="text/javascript" src="{$script}">
                </script>
            {/foreach}
            {if isset($INLINEJAVASCRIPT)}
               <script language="javascript" type="text/javascript">
                   {$INLINEJAVASCRIPT}
               </script>
            {/if}   
            {foreach from=$HEADERS item=header}{$header}{/foreach}
        {/strip}
        <link rel="stylesheet" type="text/css" href="{$THEMEURL}style/style.css">
    </head>
    <body>
        <div id="header">
            <div style="position: absolute; background-color: red; color: white;" id="loading_box"></div>
            <h1><a href="{$WWWROOT}">{$heading|default:"Mahara"|escape}</a></h1>
{if $USER}
            <a href="{$WWWROOT}?logout">Logout</a>
    {if $USER->admin}
        {if $ADMIN}
            <a href="{$WWWROOT}/">Return to Site</a>
        {else}
            <a href="{$WWWROOT}admin/">Site Administration</a>
        {/if}
    {/if}
{/if}
        </div>
{if $MAINNAV}
        <ul id="mainnav">
{foreach from=$MAINNAV item=item}
    {if $item.selected}{assign var=MAINNAVSELECTED value=$item}
            <li class="selected"><a href="{$item.link|escape}">{str section=$item.section tag=$item.name}</a></li>
    {else}
            <li><a href="{$item.link|escape}">{str section=$item.section tag=$item.name}</a></li>
    {/if}
{/foreach}
        </ul>
    {if $MAINNAVSELECTED.submenu}
        <ul id="subnav">
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
