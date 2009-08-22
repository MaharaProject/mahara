<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
{include file="header/head.tpl"}
<body>
<div class="viewheader center rel">
  <div class="rbuttons">
    <a class="small-logo" href="{$WWWROOT}"><img src="{theme_url filename='images/site-logo.png'}" alt="{$sitename|escape}"></a>
    {if $mnethost}&nbsp;&nbsp;<a href="{$mnethost.url}">{str tag=backto arg1=$mnethost.name}</a>{/if}
  </div>
  {if $can_edit}
    <div class="lbuttons"><a href="blocks.php?id={$viewid}&amp;new={$new}">{str tag=edit}</a></div>
  {/if}
  {if !$new}<a href="{$WWWROOT}view/view.php?id={$viewid}">{/if}{$viewtitle|escape}{if !$new}</a>{/if}{if $ownername} {str tag=by section=view} <a href="{$WWWROOT}{$ownerlink}">{$ownername|escape}</a>{/if}
</div>
<div id="containerX">
    <div id="loading-box" style="display: none;"></div>
    <div id="top-wrapper">
        <h1 class="hidden"><a href="{$WWWROOT}">{$hiddenheading|default:"Mahara"|escape}</a></h1>
    </div>
    <div id="main-wrapper">
        <div class="main-column">
            <div id="main-column-container">
