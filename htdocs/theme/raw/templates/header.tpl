<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html{if $LANGDIRECTION == 'rtl'} dir="rtl"{/if}>
{include file="header/head.tpl"}
<body>
{if $USERMASQUERADING}<div class="sitemessage"><img src="{theme_url filename='images/icon_problem.gif'}" alt="">{$masqueradedetails} {$becomeyouagain}</div>{/if}
{if $SITECLOSED}<div class="sitemessage center">{$SITECLOSED}</div>{/if}
<div id="container">
    <div id="loading-box"></div>
    <div id="top-wrapper">
        <h1 id="site-logo"><a href="{$WWWROOT}"><img src="{theme_url filename='images/site-logo.png'}" alt="{$sitename|escape}"></a></h1>
{include file="header/topright.tpl"}
{include file="header/navigation.tpl"}
		<div class="cb"></div>
    </div>
    <table id="main-wrapper">
        <tbody>
            <tr>
{if $SIDEBARS && $SIDEBLOCKS.left}
                <td id="left-column" class="sidebar">
{include file="sidebar.tpl" blocks=$SIDEBLOCKS.left}
                </td>
{/if}
                <td id="main-column" class="main-column">
                    {insert_messages}
                    <div id="main-column-container">

{if isset($PAGEHEADING)}                    <h1>{$PAGEHEADING}{if $PAGEHELPNAME}<span class="page-help-icon">{$PAGEHELPICON}</span>{/if}</h1>
{/if}
{if $GROUP}{* Tabs and beginning of page container for group info pages *}                        <ul class="in-page-tabs">
{foreach from=$GROUPNAV item=item}
                            <li><a {if $item.selected}class="current-tab" {/if}href="{$WWWROOT}{$item.url|escape}">{$item.title|escape}</a></li>
{/foreach}
                        </ul>
                        <div class="subpage rel">
{/if}