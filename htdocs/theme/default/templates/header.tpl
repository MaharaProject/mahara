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
            <h1>{$heading|default:"Mahara"|escape}</h1>
{if $USER}
            <a href="{$WWWROOT}?logout">Logout</a>
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
