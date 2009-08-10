<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
{include file="header/head.tpl"}
<body>
<div id="containerX">
    <div id="loading-box" style="display: none;"></div>
    <div id="top-wrapper">
        <h1 class="hidden"><a href="{$WWWROOT}">{$hiddenheading|default:"Mahara"|escape}</a></h1>
    </div>
    <div id="main-wrapper">
        <div class="main-column">
            {insert name="messages"}
            <div id="main-column-container">
{if $PAGEHEADING}                    <h1>{$PAGEHEADING}{if $PAGEHELPNAME}<span class="page-help-icon">{$PAGEHELPICON}</span>{/if}</h1>
{/if}
