<!doctype html>
<!--[if IE 8 ]><html{if $LANGDIRECTION == 'rtl'} dir="rtl"{/if} lang="{$LANGUAGE}" class="ie ie8"><![endif]-->
<!--[if IE 9 ]><html{if $LANGDIRECTION == 'rtl'} dir="rtl"{/if} lang="{$LANGUAGE}" class="ie ie9"><![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--><html{if $LANGDIRECTION == 'rtl'} dir="rtl"{/if} lang="{$LANGUAGE}"><!--<![endif]-->
{include file="header/head.tpl"}
<body data-usethemedjs="true" class="no-js {if $ADMIN || $INSTITUTIONALADMIN || $STAFF || $INSTITUTIONALSTAFF}admin{/if} {if $loggedout}loggedout{/if} {if $pagename}{$pagename}{/if}">
    <div class="skiplink btn-group btn-group-top">
        <a class="sr-only sr-only-focusable btn btn-default" href="#main">{str tag=skipmenu}</a>
    </div>

    {if $USERMASQUERADING || !$PRODUCTIONMODE || $SITECLOSED || $SITETOP}
        <div class="site-messages text-center">
    {/if}

        {if $USERMASQUERADING}
            <div class="site-message alert alert-warning" role="alert">
                <span class="icon icon-lg icon-exclamation-triangle left" role="presentation" aria-hidden="true"></span>
                <span>{$masqueradedetails}</span> 
                <a href="{$becomeyoulink}">{$becomeyouagain}</a>
            </div>
        {/if}
        {if !$PRODUCTIONMODE}
            <div class="site-message alert alert-info" role="alert">
                <span class="icon icon-lg icon-info-circle left" role="presentation" aria-hidden="true"></span>
                {str tag=notproductionsite section=error}
            </div>
        {/if}
        {if $SITECLOSED}
            <div class="site-message alert alert-danger" role="alert">
                <span class="icon icon-lg icon-lock left" role="presentation" aria-hidden="true"></span>
                {if $SITECLOSED == 'logindisabled'}{str tag=siteclosedlogindisabled section=mahara arg1="`$WWWROOT`admin/upgrade.php"}{else}{str tag=siteclosed}{/if}
            </div>
        {/if}
        {if $SITETOP}
            <div id="switchwrap">{$SITETOP|safe}</div>
        {/if}

    {if $USERMASQUERADING || !$PRODUCTIONMODE || $SITECLOSED || $SITETOP}
        </div>
    {/if}
    <header class="header navbar navbar-default navbar-fixed-top no-site-messages">
        <div class="container">
            {if $MAINNAV}
             <!-- Brand and toggle get grouped for better mobile display -->
                <button type="button" class="menu-toggle navbar-toggle collapsed" data-toggle="collapse" data-target=".nav-main">
                    <span class="sr-only">{str tag="show"} {str tag="menu"}</span>
                    <span class="icon-bar" role="presentation"></span>
                    <span class="icon-bar" role="presentation"></span>
                    <span class="icon-bar" role="presentation"></span>
                </button>
            {/if}
            {if !$nosearch && ($LOGGEDIN || $publicsearchallowed)}
            <button type="button" class="navbar-toggle search-toggle collapsed" data-toggle="collapse" data-target=".navbar-form">
                <span class="icon icon-search" role="presentation" aria-hidden="true"></span>
                <span class="nav-title sr-only">{str tag="show"} {str tag="search"}</span>
            </button>
            {/if}
            <div id="logo-area" class="logo-area">
                <a href="{$WWWROOT}" class="logo">
                    <img src="{$sitelogo}" alt="{$sitename}">
                </a>
                {if $ADMIN || $INSTITUTIONALADMIN || $STAFF || $INSTITUTIONALSTAFF}
                    <div class="admin-title">
                        <a href="{$WWWROOT}admin/" accesskey="a" class="admin-site">{str tag="administration"}</a>
                    </div>
                {/if}
                <div id="loading-box" class="loading-box" style='display:none'></div>
            </div>
            {include file="header/topright.tpl"}

        </div>
    </header>

    {include file="header/navigation.tpl"}


    <div class="container main-content">
        <div class="row">
            <main id="main" class="{if $SIDEBARS}{if $SIDEBLOCKS.right}col-md-9 {else}col-md-9 col-md-push-3{/if}{else}col-md-12{/if} main">
                <div id="content" class="main-column{if $selected == 'content'} editcontent{/if}">
                    <div id="main-column-container">

                        {if $SUBPAGENAV || $sectiontabs}
                        {assign $SUBPAGENAV item}
                        <div class="arrow-bar {$item.subnav.class}">
                            <span class="arrow hidden-xs">
                                <span class="text">
                                {if isset($PAGEHEADING)}{$PAGEHEADING}{/if}
                                </span>
                            </span>
                            <span class="right-text">
                                {include file="inpagenav.tpl"}
                            </span>
                        </div>
                        {/if}

                        {dynamic}{insert_messages}{/dynamic}
                        {if $institutionselector}
                            <div class="institutionselector">
                            {$institutionselector|safe}
                            </div>
                        {/if}

                        {if isset($PAGEHEADING)}
                            <h1 class="{$headingclass}">
                                {if isset($pageicon)}
                                <span class="{$pageicon}"></span>
                                {/if}
                                {if $SUBSECTIONHEADING}
                                <span class="section-heading">
                                    {$SUBSECTIONHEADING}
                                </span>
                                {/if}
                                <span class="section-heading">
                                    {if $SUBSECTIONHEADING}| {/if}{$PAGEHEADING}
                                </span>
                                {if $PAGEHELPNAME}<span class="page-help-icon">{$PAGEHELPICON|safe}</span>{/if}
                                {if $publicgroup && $rsswithtitle}
                                <a href="{$feedlink}" class="mahara-rss-icon text-small pull-right " role="presentation" aria-hidden="true">
                                    <span class="icon-rss icon icon-lg" role="presentation" aria-hidden="true"></span>
                                </a>
                                {/if}
                            </h1>
                        {/if}

                        {if $SUBPAGETOP}
                            {include file=$SUBPAGETOP}
                        {/if}
