<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<!--[if IE 8 ]>    <html{if $LANGDIRECTION == 'rtl'} dir="rtl"{/if} lang="{$LANGUAGE}" class="ie ie8"> <![endif]-->
<!--[if IE 9 ]>    <html{if $LANGDIRECTION == 'rtl'} dir="rtl"{/if} lang="{$LANGUAGE}" class="ie ie9"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!-->  <html{if $LANGDIRECTION == 'rtl'} dir="rtl"{/if} lang="{$LANGUAGE}"><!--<![endif]-->
<html{if $LANGDIRECTION == 'rtl'} dir="rtl"{/if}>
{include file="header/head.tpl"}
<body id="micro">
{if $USERMASQUERADING || !$PRODUCTIONMODE || $SITECLOSED || $SITETOP}<div class="sitemessages">{/if}
    {if $USERMASQUERADING}<div class="sitemessage"><img src="{theme_url filename='images/failure.png'}" alt="">{$masqueradedetails} {$becomeyouagain|safe}</div>{/if}
    {if !$PRODUCTIONMODE}<div class="sitemessage center">{str tag=notproductionsite section=error}</div>{/if}
    {if $SITECLOSED}<div class="sitemessage center">{if $SITECLOSED == 'logindisabled'}{str tag=siteclosedlogindisabled section=mahara arg1="`$WWWROOT`admin/upgrade.php"}{else}{str tag=siteclosed}{/if}</div>{/if}
    {if $SITETOP}<div id="switchwrap">{$SITETOP|safe}</div>{/if}
{if $USERMASQUERADING || !$PRODUCTIONMODE || $SITECLOSED || $SITETOP}</div>{/if}
<div id="container">
    <div class="center"><a class="skiplink" href="#viewheader">{str tag=skipmenu}</a></div>
    <div id="loading-box"></div>
    <div id="top-wrapper">
        <div id="header">
            <div class="viewheadertop">
            <div class="fl">
                <a class="small-logo" href="{$WWWROOT}"><img src="{theme_url filename=$maharalogofilename}" alt="{$sitename}"></a>
            </div>
            {if $LOGGEDIN}
            <div id="mainnav-container" class="nav">
                <div id="mainnav">
                    <strong><a href="{profile_url($USER)}">{$USER|display_name:null:true}</a>:</strong>
                    {foreach from=$MAINNAV item=item}
                        {if $item.path}
                        <a href="{$WWWROOT}{$item.url}">{$item.title}</a> |
                        {/if}
                    {/foreach}
                    {if $USER->get('admin')}
                    <a href="{$WWWROOT}admin/index.php">{str tag="administration"}</a> |
                    {elseif $USER->is_institutional_admin()}
                    <a href="{$WWWROOT}admin/users/search.php">{str tag="administration"}</a> |
                    {/if}
                    {if $mnethost}<a href="{$mnethost.url}">{str tag=backto arg1=$mnethost.name}</a>&nbsp;{/if}
                    <a href="{$WWWROOT}?logout">{str tag="logout"}</a>
                </div>
            </div>
            {/if}
            <div class="fr links">
                {if $microheaderlinks}
                    {foreach from=$microheaderlinks item=item}
                        <a class="btn" href="{$item.url}">{$item.name}</a>
                    {/foreach}
                {/if}
                <a class="btn nojs-hidden-inline" href="javascript:history.back()"><span class="btn-back">{str tag=back}</span></a>
            </div>
        </div>
        <div id="viewheader" class="viewheader">
        {if $collection}
            <div id="collection"><h1 class="collection-title">{$microheadertitle|safe}</h1>{include file=collectionnav.tpl}</div>
        {else}
            <h1 class="center viewtitle">{$microheadertitle|safe}</h1>
        {/if}
        <div class="cb"></div>
      </div>
    </div>
</div>
<div class="cb"></div>
<div id="mainmiddlewrap">
    <div id="mainmiddle">
      <div id="main-wrapper">
          <div class="main-column">
              <div id="main-column-container">
              {dynamic}{insert_messages}{/dynamic}
