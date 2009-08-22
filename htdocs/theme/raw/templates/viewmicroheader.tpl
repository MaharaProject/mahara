<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
{include file="header/head.tpl"}
<body>
<div class="viewheader center rel">
  <div class="lbuttons">
    <a class="small-logo" href="{$WWWROOT}"><img src="{theme_url filename='images/site-logo.png'}" alt="{$sitename|escape}"></a>
    {if $mnethost}<a href="{$mnethost.url|escape}">{str tag=backto arg1=$mnethost.name}</a>{/if}
    {if $backurl}<a class="btn-reply" href="{$backurl|escape}">{str tag=back}</a>{/if}
  </div>
  {if $edit_url}
    <div class="rbuttons"><strong><a href="{$edit_url|escape}" class="btn-edit">{str tag=edit}</a></strong></div>
  {/if}
  <h1>{$viewtitle}</h1>
</div>
<div id="dropshadow"></div>
<div id="containerX">
    <div id="loading-box" style="display: none;"></div>
    <div id="top-wrapper">
        <h1 class="hidden"><a href="{$WWWROOT}">{$hiddenheading|default:"Mahara"|escape}</a></h1>
    </div>
    <div id="main-wrapper">
        <div class="main-column">
            <div id="main-column-container">
