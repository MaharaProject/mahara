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
          {if $mnethost}&nbsp;&nbsp;<a href="{$mnethost.url}">{str tag=backto arg1=$mnethost.name}</a>{/if}
        </div>
        <div class="lbuttons">
          {if $backurl}<a class="btn-reply" href="{$backurl}">{str tag=back}</a>&nbsp;{/if}
          {if $can_edit}<a class="btn-edit" href="blocks.php?id={$viewid}&amp;new={$new}">{str tag=edit}</a>{/if}
        </div>
        <div class="center">
        {if !$new}<a href="{$WWWROOT}view/view.php?id={$viewid}">{/if}{$viewtitle|escape}{if !$new}</a>{/if}{if $ownername} {str tag=by section=view} <a href="{$WWWROOT}{$ownerlink}">{$ownername|escape}</a>{/if}</div>
      </div>
      <!--div id="dropshadow"></div-->
    </div>
    <div id="main-wrapper">
        <div class="main-column">
            {dynamic}{insert_messages}{/dynamic}
            <div id="main-column-container">
