<!doctype html>
<!--[if IE 8 ]><html{if $LANGDIRECTION == 'rtl'} dir="rtl"{/if} lang="{$LANGUAGE}" class="ie ie8"><![endif]-->
<!--[if IE 9 ]><html{if $LANGDIRECTION == 'rtl'} dir="rtl"{/if} lang="{$LANGUAGE}" class="ie ie9"><![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--><html{if $LANGDIRECTION == 'rtl'} dir="rtl"{/if} lang="{$LANGUAGE}"><!--<![endif]-->
{include file="header/head.tpl"}
<body data-usethemedjs="true" class="no-js {if $ADMIN || $INSTITUTIONALADMIN || $STAFF || $INSTITUTIONALSTAFF}admin{/if} {if $loggedout}loggedout{/if} {if $pagename}{$pagename}{/if} {$presentation|default:'window'}">
    <div class="skiplink btn-group btn-group-top">
        <a class="sr-only sr-only-focusable btn btn-secondary" {if $headertype=='page'}href="#header-target-main"{else}href="#header-main"{/if}>{str tag=skipmenu}</a>
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
        {if $SITEOUTOFSYNC}
            <div class="site-message alert alert-warning" role="alert">
                <span class="icon icon-lg icon-info-circle left" role="presentation" aria-hidden="true"></span>
                {str tag=siteoutofsyncfor section=error arg1=$SITEOUTOFSYNC}
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

    <header class="header fixed-top no-site-messages">
        <div class="navbar navbar-default navbar-main">
            <div class="container">
                <div id="logo-area" class="logo-area">
                    <a href="{$WWWROOT}" class="logo {if $sitelogocustomsmall || (!$sitelogocustomsmall && !$sitelogocustom)}change-to-small{/if}">
                        <img src="{$sitelogo}" alt="{$sitename}" data-customlogo="{$sitelogocustom}" >
                    </a>
                    {if $sitelogocustomsmall}
                        <a href="{$WWWROOT}" class="logoxs">
                            <img src="{$sitelogocustomsmall}" alt="{$sitename}">
                        </a>
                    {/if}
                    {if !$sitelogocustom && !$sitelogocustomsmall}
                        <a href="{$WWWROOT}" class="logoxs change-to-small-default">
                            <img src="{$sitelogosmall}" alt="{$sitename}">
                        </a>
                    {/if}
                    {if $ADMIN || $INSTITUTIONALADMIN || $STAFF || $INSTITUTIONALSTAFF}
                        <div class="admin-title">
                            <a href="{$WWWROOT}admin/" class="admin-site">{str tag="administration"}</a>
                        </div>
                    {/if}
                    <div id="loading-box" class="loading-box d-none"></div>
                </div>
                    <div class="nav-toggle-area">
                        {if $MAINNAV}
                            <button class="main-nav-toggle navbar-toggle collapsed" type="button" data-toggle="collapse" data-target=".nav-main" aria-expanded="false" aria-controls="main-nav" title='{str tag="mainmenu"}'>
                                <span class="sr-only">{str tag="showmainmenu"}</span>
                                <span class="icon icon-bars icon-lg" role="presentation" aria-hidden="true"></span>
                            </button>
                        {/if}
                        {if $MAINNAVADMIN}
                            <button class="admin-toggle navbar-toggle collapsed" type="button" data-toggle="collapse" data-target=".nav-main-admin" aria-expanded="false" aria-controls="main-nav-admin" title='{str tag="adminmenu"}'>
                                <span class="sr-only">{str tag="showadminmenu"}</span>
                                <span class="icon icon-wrench icon-large" role="presentation" aria-hidden="true"></span>
                            </button>
                        {/if}
                        {if $LOGGEDIN}
                            <a href="{profile_url($USER)}" class="user-icon user-icon-25" title='{str tag="profilepage"}'>
                                <img src="{profile_icon_url user=$USER maxheight=25 maxwidth=25}" alt="{str tag=profileimagefor section=artefact.internal arg1=display_name($USER->get('id'))}">
                            </a>
                            <button class="user-toggle navbar-toggle" type="button" data-toggle="collapse" data-target=".nav-main-user" aria-expanded="false" aria-controls="main-nav-user" title='{str tag="usermenu"}'>
                                <span class="sr-only">{str tag="showusermenu"}</span>
                                <span class="icon icon-chevron-down collapsed"></span>
                            </button>
                        {/if}
                        {if $MESSAGEBOX}
                            {foreach from=$MESSAGEBOX item=item}
                            <a href="{$WWWROOT}{$item.url}" title="{$item.alt}" role="button" id="nav-{$item.path}" class="navbar-toggle navbar-messages collapsed">
                                <span class="sr-only">{$item.title} <span class="{$item.countclasssr}">{$item.unread}</span></span>
                                <span class="icon icon-{$item.iconclass} icon-lg" role="presentation" aria-hidden="true"></span>
                                {if $item.count}
                                    <span class="navbar-messages-count">
                                        <span class="{$item.countclass}">{$item.count}</span>
                                    </span>
                                {/if}
                            </a>
                            {/foreach}
                        {/if}
                        <!-- HIDE WHEN ON DESKTOP -->
                        {if !$nosearch && ($LOGGEDIN || $publicsearchallowed)}
                        <button class="search-toggle navbar-toggle collapsed" type="button" data-toggle="collapse" data-target=".navbar-form" aria-expanded="false" aria-controls="usf">
                            <span class="icon icon-search icon-lg" role="presentation" aria-hidden="true"></span>
                            <span class="nav-title sr-only">{str tag="showsearch"}</span>
                        </button>
                        {/if}
                    </div>

                    {include file="header/topright.tpl"}
                    {include file="header/navigation.tpl"}
            </div>
        </div>
    </header>

    {if $headertype == "page"}
        {include file="header/pageheader.tpl"}
    {elseif $headertype == "profile"}
        {include file="header/profileheader.tpl"}
    {elseif $headertype == "matrix"}
        {include file="header/matrixheader.tpl"}
    {/if}

    <div class="container main-content">
        <div class="row">
            <a id="header-main"></a>
            <main id="main" class="{if $SIDEBARS}{if $SIDEBLOCKS.right}col-lg-9 {else}col-lg-9 order-md-2 {/if}{else}col-md-12{/if} main">
                <div id="content" class="main-column{if $selected == 'content'} editcontent{/if}">
                    <div id="main-column-container">

                        {if $SUBPAGENAV || $sectiontabs}
                        {assign $SUBPAGENAV item}
                        <div class="arrow-bar {$item.subnav.class}">
                            <span class="arrow d-none d-md-block">
                                <span class="text">
                                {if isset($PAGEHEADINGARROW)}
                                    {$PAGEHEADINGARROW}
                                {elseif isset($PAGEHEADING)}
                                    {$PAGEHEADING}
                                {/if}
                                </span>
                            </span>
                            <div class="right-text">
                                {include file="inpagenav.tpl"}
                            </div>
                        </div>
                        {/if}

                        {dynamic}{$messages.messages|safe}{/dynamic}
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
                                <a href="{$feedlink}" class="mahara-rss-icon text-small float-right " role="presentation" aria-hidden="true">
                                    <span class="icon-rss icon icon-lg" role="presentation" aria-hidden="true"></span>
                                </a>
                                {/if}
                            </h1>
                        {/if}

                        {if $SUBPAGETOP}
                            {include file=$SUBPAGETOP}
                        {/if}
