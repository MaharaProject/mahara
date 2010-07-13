<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html{if $LANGDIRECTION == 'rtl'} dir="rtl"{/if}>
{include file="header/head.tpl"}
<body>
{if $USERMASQUERADING}<div class="sitemessage"><img src="{theme_url filename='images/icon_problem.gif'}" alt="">{$masqueradedetails} {$becomeyouagain|safe}</div>{/if}
{if $SITECLOSED}<div class="sitemessage center">{if $SITECLOSED == 'logindisabled'}{str tag=siteclosedlogindisabled section=mahara arg1="`$WWWROOT`admin/upgrade.php"}{else}{str tag=siteclosed}{/if}</div>{/if}
<div id="containerX">
    <div id="loading-box"></div>
    <div id="top-wrapper">
      <div class="viewheader rel">
        <div class="links rbuttons">
          {if $microheaderlinks}
            {foreach from=$microheaderlinks item=item}
              <a {if $item.type}class="btn-{$item.type}" {/if}href="{$item.url}">{$item.name}</a>&nbsp;
            {/foreach}
          {elseif $backurl}<a class="btn-reply" href="{$backurl}">{str tag=back}</a>&nbsp;
          {/if}
        </div>
        <div class="lbuttons">
          <a class="small-logo" href="{$WWWROOT}"><img src="{theme_url filename='images/site-logo-small.png'}" alt="{$sitename}"></a>
        </div>
{if $LOGGEDIN}
        <div class="nav">
          <a href="{$WWWROOT}user/view.php">{$USER|display_name:null:true|escape}</a>&nbsp;:
          {foreach from=$MAINNAV item=item}
            {if $item.path}
              <a href="{if $item.url=='account/' && get_config('httpswwwroot')}{$HTTPSWWWROOT}{else}{$WWWROOT}{/if}{$item.url}">{$item.title}</a>&nbsp;
            {/if}
          {/foreach}
          {if $USER->get('admin')}
            <a href="{$WWWROOT}admin/">{str tag="siteadministration"}</a>&nbsp;
          {elseif $USER->is_institutional_admin()}
            <a href="{$WWWROOT}admin/users/search.php">{str tag="useradministration"}</a>&nbsp;
          {/if}

          {if $mnethost}<a href="{$mnethost.url}">{str tag=backto arg1=$mnethost.name}</a>&nbsp;{/if}
          <a href="{$WWWROOT}?logout">{str tag="logout"}</a>
        </div>
{/if}
        <div class="center cb title">{$microheadertitle|safe}</div>
      </div>
    </div>
    <div id="main-wrapper">
        <div class="main-column">
            {dynamic}{insert_messages}{/dynamic}
            <div id="main-column-container">
