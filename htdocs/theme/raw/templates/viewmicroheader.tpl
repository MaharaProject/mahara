<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html{if $LANGDIRECTION == 'rtl'} dir="rtl"{/if}>
{include file="header/head.tpl"}
<body>
{if $USERMASQUERADING}<div class="sitemessage"><img src="{theme_url filename='images/icon_problem.gif'}" alt="">{$masqueradedetails} {$becomeyouagain}</div>{/if}
{if $SITECLOSED}<div class="sitemessage center">{$SITECLOSED}</div>{/if}
<div id="containerX">
    <div id="loading-box"></div>
    <div id="top-wrapper">
      <div class="viewheader rel">
        <div class="rbuttons">
          <a class="small-logo" href="{$WWWROOT}"><img src="{theme_url filename='images/site-logo-small.png'}" alt="{$sitename|escape}"></a>
        </div>
        <div class="links lbuttons">
          {if $microheaderlinks}
            {foreach from=$microheaderlinks item=item}
              <a {if $item.edit}class="btn-edit" {/if}href="{$item.url}">{$item.name|escape}</a>&nbsp;
            {/foreach}
          {elseif $backurl}<a class="btn-reply" href="{$backurl}">{str tag=back}</a>&nbsp;
          {/if}
        </div>
        <div class="nav">
{if $LOGGEDIN}
          <a href="{$WWWROOT}user/view.php">{$userdisplayname|escape}</a>&nbsp;:
          {foreach from=$MAINNAV item=item}
            {if $item.path}
              <a href="{if get_config('httpswwwroot') && $item.url=='account/'}{$HTTPSWWWROOT}{else}{$WWWROOT}{/if}{$item.url|escape}">{$item.title|escape}</a>&nbsp;
            {/if}
          {/foreach}
          {if $mnethost}<a href="{$mnethost.url}">{str tag=backto arg1=$mnethost.name}</a>&nbsp;{/if}
{/if}
        </div>
        <div class="center cb title">
        <strong>{$viewtitle|escape}</strong>{if $ownername && $viewtype != 'profile'} {str tag=by section=view} <a href="{$WWWROOT}{$ownerlink}">{$ownername|escape}</a>{/if}</div>
      </div>
    </div>
    <div id="main-wrapper">
        <div class="main-column">
            {dynamic}{insert_messages}{/dynamic}
            <div id="main-column-container">
