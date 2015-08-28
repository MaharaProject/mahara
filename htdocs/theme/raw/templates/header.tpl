<!doctype html>
<!--[if IE 8 ]><html{if $LANGDIRECTION == 'rtl'} dir="rtl"{/if} lang="{$LANGUAGE}" class="ie ie8"><![endif]-->
<!--[if IE 9 ]><html{if $LANGDIRECTION == 'rtl'} dir="rtl"{/if} lang="{$LANGUAGE}" class="ie ie9"><![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--><html{if $LANGDIRECTION == 'rtl'} dir="rtl"{/if} lang="{$LANGUAGE}"><!--<![endif]-->
{include file="header/head.tpl"}
<body data-usethemedjs="true" class="no-js {if $ADMIN || $INSTITUTIONALADMIN || $STAFF || $INSTITUTIONALSTAFF}admin{/if} {if $loggedout}loggedout{/if}">
    <a class="sr-only sr-only-focusable" href="#main">{str tag=skipmenu}</a>

    {if $USERMASQUERADING || !$PRODUCTIONMODE || $SITECLOSED || $SITETOP}
        <div class="site-messages text-center">
    {/if}

        {if $USERMASQUERADING}
            <div class="site-message alert alert-warning" role="alert">
                <span class="icon icon-lg icon-exclamation-triangle prm"></span>
                {$masqueradedetails} {$becomeyouagain|safe}
            </div>
        {/if}
        {if !$PRODUCTIONMODE}
            <div class="site-message alert alert-info" role="alert">
                <span class="icon icon-lg icon-info-circle prm"></span>
                {str tag=notproductionsite section=error}
            </div>
        {/if}
        {if $SITECLOSED}
            <div class="site-message alert alert-danger" role="alert">
                <span class="icon icon-lg icon-lock prm"></span>
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
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
            {/if}
            {if !$nosearch && ($LOGGEDIN || $publicsearchallowed)}
            <button type="button" class="navbar-toggle search-toggle collapsed" data-toggle="collapse" data-target=".navbar-form">
                <span class="icon icon-search"></span>
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
            <div id="loading-box" class="loading-box" style='display:none'></div>
            {include file="header/topright.tpl"}

        </div>
    </header>

    {include file="header/navigation.tpl"}


    <div class="container main-content">
        <div class="row">
            <div id="main" class="{if $SIDEBARS}{if $SIDEBLOCKS.right}col-md-9 {else}col-md-9 col-md-push-3{/if}{else}col-md-12{/if} main">
                <div id="content" class="main-column{if $selected == 'content'} editcontent{/if}">
                    <div id="main-column-container">

                        {if $SUBPAGENAV || $sectiontabs}
                            <div class="arrow-bar">
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
                            <div class="pull-right institutionselector">
                            {$institutionselector|safe}
                            </div>
                        {/if}
                        {if isset($PAGEHEADING)}

                            <h1 class="{$headingclass}">
                                {if isset($pageicon)}
                                <span class="{$pageicon}"></span>
                                {/if}
                                {if $subsectionheading}
                                <span class="subsection-heading">
                                    {$subsectionheading}
                                </span>
                                {/if}
                                <span class="section-heading">
                                    {if $subsectionheading}| {/if}{$PAGEHEADING}
                                </span>
                                {if $PAGEHELPNAME}<span class="page-help-icon">{$PAGEHELPICON|safe}</span>{/if}
                                {if $publicgroup && $rsswithtitle}
                                <a href="{$feedlink}" class="text-orange text-small pull-right ">
                                    <span class="icon-rss icon icon-lg"></span>
                                </a>
                                {/if}


                            </h1>

                        {/if}

                        {if $SUBPAGETOP}
                            {include file=$SUBPAGETOP}
                        {/if}
