<!doctype html>
<!--[if IE 8 ]><html{if $LANGDIRECTION == 'rtl'} dir="rtl"{/if} lang="{$LANGUAGE}" class="ie ie8"><![endif]-->
<!--[if IE 9 ]><html{if $LANGDIRECTION == 'rtl'} dir="rtl"{/if} lang="{$LANGUAGE}" class="ie ie9"><![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--><html{if $LANGDIRECTION == 'rtl'} dir="rtl"{/if} lang="{$LANGUAGE}"><!--<![endif]-->
{include file="header/head.tpl"}
<body data-usethemedjs="true" class="no-js {if $ADMIN || $INSTITUTIONALADMIN || $STAFF || $INSTITUTIONALSTAFF}admin{/if}">
    <a class="sr-only sr-only-focusable" href="#main">{str tag=skipmenu}</a>

    {if $USERMASQUERADING || !$PRODUCTIONMODE || $SITECLOSED || $SITETOP}
        <div class="sitemessages">
    {/if}

        {if $USERMASQUERADING}
            <div class="sitemessage alert alert-info" role="alert"><img src="{theme_url filename='images/failure.png'}" alt="">{$masqueradedetails} {$becomeyouagain|safe}</div>
        {/if}
        {if !$PRODUCTIONMODE}
            <div class="sitemessage alert alert-info" role="alert">{str tag=notproductionsite section=error}</div>
        {/if}
        {if $SITECLOSED}
        <div class="sitemessage alert alert-info" role="alert">
            {if $SITECLOSED == 'logindisabled'}{str tag=siteclosedlogindisabled section=mahara arg1="`$WWWROOT`admin/upgrade.php"}{else}{str tag=siteclosed}{/if}</div>
        {/if}
        {if $SITETOP}
            <div id="switchwrap">{$SITETOP|safe}</div>
        {/if}

    {if $USERMASQUERADING || !$PRODUCTIONMODE || $SITECLOSED || $SITETOP}
        </div>
    {/if}

    <div id="loading-box" class="loading-box"></div>

    <header class="header navbar navbar-default navbar-fixed-top">
        <div class="container">
            {if $MAINNAV}
             <!-- Brand and toggle get grouped for better mobile display -->
                <button type="button" class="menu-toggle navbar-toggle collapsed" data-toggle="collapse" data-target=".nav-main">
                    <span class="sr-only">{str tag="show"} {str tag="menu"}</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
            {/if}
            {if !$nosearch && ($LOGGEDIN || $publicsearchallowed)}
            <button type="button" class="navbar-toggle search-toggle collapsed" data-toggle="collapse" data-target=".navbar-form">
                <span class="glyphicon glyphicon-search"></span>
                <span class="nav-title sr-only">{str tag="show"} {str tag="search"}</span>
            </button>
            {/if}
            <span id="site-logo" class="site-logo">
                    <a href="{$WWWROOT}">
                        <img src="{$sitelogo}" alt="{$sitename}">
                    </a>
                    {if $ADMIN || $INSTITUTIONALADMIN || $STAFF || $INSTITUTIONALSTAFF}
                        <div class="admin-title">
                            <a href="{$WWWROOT}admin/" accesskey="a" class="admin-site">{str tag="administration"}</a>
                        </div>
                    {/if}
            </span>
            {include file="header/topright.tpl"}
        </div>
    </header>

    {include file="header/navigation.tpl"}

    <div class="container">
        {if $USERMASQUERADING || !$PRODUCTIONMODE || $SITECLOSED || $SITETOP}
            <div class="sitemessages">
        {/if}
            {if $USERMASQUERADING}
                <div class="sitemessage alert alert-danger" role="alert"><img src="{theme_url filename='images/failure.png'}" alt="">{$masqueradedetails} {$becomeyouagain|safe}</div>
            {/if}
            {if !$PRODUCTIONMODE}
                <div class="sitemessage alert alert-danger" role="alert">{str tag=notproductionsite section=error}</div>
            {/if}
            {if $SITECLOSED}
            <div class="sitemessage alert alert-info" role="alert">
                {if $SITECLOSED == 'logindisabled'}{str tag=siteclosedlogindisabled section=mahara arg1="`$WWWROOT`admin/upgrade.php"}{else}{str tag=siteclosed}{/if}</div>
            {/if}
            {if $SITETOP}
                <div id="switchwrap">{$SITETOP|safe}</div>
            {/if}

        {if $USERMASQUERADING || !$PRODUCTIONMODE || $SITECLOSED || $SITETOP}
            </div>
        {/if}
        <div class="row">
            <div id="main" class="{if $SIDEBARS}{if $SIDEBLOCKS.right}col-md-9 {else}col-md-9 col-md-push-3{/if}{else}col-md-12{/if} main">
                <div id="content" class="main-column{if $selected == 'content'} editcontent{/if}">
                    <div id="main-column-container">

                        {dynamic}{insert_messages}{/dynamic}
                        {if isset($PAGEHEADING)}
                            <h1>{$PAGEHEADING}{if $PAGEHELPNAME}<span class="page-help-icon">{$PAGEHELPICON|safe}</span>{/if}</h1>
                        {/if}

                        {if $SUBPAGENAV}
                            {if $SUBPAGETOP}
                                {include file=$SUBPAGETOP}
                            {/if}
                            {* Tabs and beginning of page container for group info pages *}
                                <ul class="nav nav-pills">
                                    {foreach from=$SUBPAGENAV item=item}
                                        <li {if $item.selected}class="current-tab active" role="presentation"{/if}>
                                            <a {if $item.tooltip}title="{$item.tooltip}"{/if}{if $item.selected}class="current-tab" {/if}href="{$WWWROOT}{$item.url}">
                                                {$item.title}
                                                <span class="accessible-hidden sr-only">({str tag=tab}{if $item.selected} {str tag=selected}{/if})</span>
                                            </a>
                                        </li>
                                    {/foreach}
                                </ul>
                            <div class="subpage">
                        {/if}
