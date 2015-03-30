<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<!--[if IE 8 ]><html{if $LANGDIRECTION == 'rtl'} dir="rtl"{/if} lang="{$LANGUAGE}" class="ie ie8"><![endif]-->
<!--[if IE 9 ]><html{if $LANGDIRECTION == 'rtl'} dir="rtl"{/if} lang="{$LANGUAGE}" class="ie ie9"><![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--><html{if $LANGDIRECTION == 'rtl'} dir="rtl"{/if} lang="{$LANGUAGE}"><!--<![endif]-->
{include file="header/head.tpl"}
<body class="no-js">
{if $ADDITIONALHTMLTOPOFBODY}{$ADDITIONALHTMLTOPOFBODY|safe}{/if}
{if $USERMASQUERADING || !$PRODUCTIONMODE || $SITECLOSED || $SITETOP}<div class="sitemessages">{/if}
    {if $USERMASQUERADING}<div class="sitemessage"><img src="{theme_image_url filename='failure'}" alt="">{$masqueradedetails} {$becomeyouagain|safe}</div>{/if}
    {if !$PRODUCTIONMODE}<div class="sitemessage center">{str tag=notproductionsite section=error}</div>{/if}
    {if $SITECLOSED}<div class="sitemessage center">{if $SITECLOSED == 'logindisabled'}{str tag=siteclosedlogindisabled section=mahara arg1="`$WWWROOT`admin/upgrade.php"}{else}{str tag=siteclosed}{/if}</div>{/if}
    {if $SITETOP}<div id="switchwrap">{$SITETOP|safe}</div>{/if}
{if $USERMASQUERADING || !$PRODUCTIONMODE || $SITECLOSED || $SITETOP}</div>{/if}
<div id="container">
    <div class="center"><a class="skiplink" href="#mainmiddle">{str tag=skipmenu}</a></div>
    <div id="loading-box"></div>
    <div id="top-wrapper">
        <div id="header"><h1 id="site-logo"><a href="{$WWWROOT}"><img src="{$sitelogo}" alt="{$sitename}"></a></h1>
{include file="header/topright.tpl"}
        </div>
{include file="header/navigation.tpl"}
        <div class="cb"></div>
    </div>
    <div id="mainmiddlewrap">
        <div id="mainmiddle">
            <div id="{if $SIDEBARS}{if $SIDEBLOCKS.right}main-wrapper-narrow-right{else}main-wrapper-narrow-left{/if}{else}main-wrapper{/if}">
                    <div id="main-column" class="main-column{if $SIDEBARS} main-column-narrow{/if}{if $selected == 'content'} editcontent{/if}">
                    <div id="main-column-container">
                        {dynamic}{insert_messages}{/dynamic}
{if isset($PAGEHEADING)}
                       <h1>{$PAGEHEADING}{if $PAGEHELPNAME}<span class="page-help-icon">{$PAGEHELPICON|safe}</span>{/if}</h1>
{/if}

{if $SUBPAGENAV}{if $SUBPAGETOP}
                       {include file=$SUBPAGETOP}
{/if}
{* Tabs and beginning of page container for group info pages *}
                       <div class="tabswrap">
                           <ul class="in-page-tabs">
{foreach from=$SUBPAGENAV item=item}
                               <li {if $item.selected}class="current-tab"{/if}><a {if $item.tooltip}title="{$item.tooltip}" {/if}{if $item.selected}class="current-tab" {/if}href="{$WWWROOT}{$item.url}">{$item.title}<span class="accessible-hidden">({str tag=tab}{if $item.selected} {str tag=selected}{/if})</span></a></li>
{/foreach}
                           </ul>
                       </div>
                        <div class="subpage">
{/if}
