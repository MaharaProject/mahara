<!doctype html>
<!--[if IE 8 ]><html{if $LANGDIRECTION == 'rtl'} dir="rtl"{/if} lang="{$LANGUAGE}" class="ie ie8"><![endif]-->
<!--[if IE 9 ]><html{if $LANGDIRECTION == 'rtl'} dir="rtl"{/if} lang="{$LANGUAGE}" class="ie ie9"><![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--><html{if $LANGDIRECTION == 'rtl'} dir="rtl"{/if} lang="{$LANGUAGE}"><!--<![endif]-->
{include file="header/head.tpl"}
<body class="no-js">
    {if $ADDITIONALHTMLTOPOFBODY}{$ADDITIONALHTMLTOPOFBODY|safe}{/if}
    
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

    <a class="sr-only sr-only-focusable" href="#mainmiddle">{str tag=skipmenu}</a>

    <div id="loading-box" class="loading-box"></div>

    <header class="header">
        <div class="container">
            <h1 id="site-logo">
                <a href="{$WWWROOT}">
                    <img src="{$sitelogo}" alt="{$sitename}">
                </a>
            </h1>
            {include file="header/topright.tpl"}
        </div>

        {include file="header/navigation.tpl"}

        <div class="cb"></div>
    </header>

    <div id="mainmiddle" class="container" role="main">

        <div class="row">

        <div class="{if $SIDEBARS}{if $SIDEBLOCKS.right}col-md-9{else}col-md-9 col-md-push-3{/if}{else}col-md-12{/if}">
            
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
                        <div class="tabswrap">
                            <ul class="in-page-tabs">
                                {foreach from=$SUBPAGENAV item=item}
                                    <li {if $item.selected}class="current-tab"{/if}>
                                        <a {if $item.tooltip}title="{$item.tooltip}"{/if}{if $item.selected}class="current-tab" {/if}href="{$WWWROOT}{$item.url}">
                                            {$item.title}
                                            <span class="accessible-hidden">({str tag=tab}{if $item.selected} {str tag=selected}{/if})</span>
                                        </a>
                                    </li>
                                {/foreach}
                                </ul>
                        </div>
                        <div class="subpage">
                    {/if}
