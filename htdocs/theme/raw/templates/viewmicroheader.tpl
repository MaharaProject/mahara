<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html{if $LANGDIRECTION == 'rtl'} dir="rtl"{/if}>
{include file="header/head.tpl"}
<body>
{if $USERMASQUERADING}<div class="sitemessage"><img src="{theme_url filename='images/icon_problem.gif'}" alt="">{$masqueradedetails} {$becomeyouagain}</div>{/if}
{if $SITECLOSED}<div class="sitemessage center">{$SITECLOSED}</div>{/if}
<div id="containerX">
    <div id="loading-box"></div>
    <div id="top-wrapper">
      <div class="viewheader">
        <div class="nav">
{if $LOGGEDIN}
          <a href="{$WWWROOT}user/view.php">{$userdisplayname|escape}</a>&nbsp;
          {foreach from=$MAINNAV item=item}
            {if $item.path}
              <a href="{if get_config('httpswwwroot') && $item.url=='account/'}{$HTTPSWWWROOT}{else}{$WWWROOT}{/if}{$item.url|escape}">{$item.title|escape}</a>&nbsp;
            {/if}
          {/foreach}
          {if $mnethost}<a href="{$mnethost.url}">{str tag=backto arg1=$mnethost.name}</a>&nbsp;{/if}
{/if}
          <a class="small-logo" href="{$WWWROOT}"><img src="{theme_url filename='images/site-logo-small.png'}" alt="{$sitename|escape}"></a>
        </div>
{if $LOGGEDIN}
        <div class="links">
          {if $backurl}<a class="btn-reply" href="{$backurl}">{str tag=back}</a>&nbsp;{/if}
          {if $edit_profile || $viewtype == 'profile'}
              <a href="{$WWWROOT}user/view.php">{str tag=viewmyprofilepage}</a>&nbsp;
              <a class="btn-edit" href="{$WWWROOT}view/blocks.php?profile=1">{str tag=editmyprofilepage}</a>&nbsp;
              <a class="btn-edit" href="{$WWWROOT}artefact/internal/index.php">{str tag=editprofile section=artefact.internal}</a>
          {elseif !empty($viewtype)}
              <a href="{$WWWROOT}view/index.php">{str tag=myviews}</a>&nbsp;
              <a class="btn-edit" href="{$WWWROOT}view/edit.php?id={$viewid}&amp;new={$new}">{str tag=editdetails section=view}</a>
              <a class="btn-edit" href="{$WWWROOT}view/access.php?id={$viewid}&amp;new={$new}">{str tag=editaccess section=view}</a>
          {elseif $can_edit}
              <a class="btn-edit" href="{$WWWROOT}view/blocks.php?id={$viewid}&amp;new={$new}">{str tag=edit}</a>
          {/if}
        </div>
{/if}
        <div class="center cb title">
        <strong>{$viewtitle|escape}</strong>{if $ownername && $viewtype != 'profile'} {str tag=by section=view} <a href="{$WWWROOT}{$ownerlink}">{$ownername|escape}</a>{/if}</div>
      </div>
    </div>
    <div id="main-wrapper">
        <div class="main-column">
            {dynamic}{insert_messages}{/dynamic}
            <div id="main-column-container">
