<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<!--[if lt IE 7 ]> <html{if $LANGDIRECTION == 'rtl'} dir="rtl"{/if} class="ie ie6"> <![endif]-->
<!--[if IE 7 ]>    <html{if $LANGDIRECTION == 'rtl'} dir="rtl"{/if} class="ie ie7"> <![endif]-->
<!--[if IE 8 ]>    <html{if $LANGDIRECTION == 'rtl'} dir="rtl"{/if} class="ie ie8"> <![endif]-->
<!--[if IE 9 ]>    <html{if $LANGDIRECTION == 'rtl'} dir="rtl"{/if} class="ie ie9"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!-->  <html{if $LANGDIRECTION == 'rtl'} dir="rtl"{/if}><!--<![endif]-->
<html{if $LANGDIRECTION == 'rtl'} dir="rtl"{/if}>
{include file="header/head.tpl"}
<body id="micro">
{if $USERMASQUERADING}<div class="sitemessage"><img src="{theme_url filename='images/icon_problem.gif'}" alt="">{$masqueradedetails} {$becomeyouagain|safe}</div>{/if}
{if $SITECLOSED}<div class="sitemessage center">{if $SITECLOSED == 'logindisabled'}{str tag=siteclosedlogindisabled section=mahara arg1="`$WWWROOT`admin/upgrade.php"}{else}{str tag=siteclosed}{/if}</div>{/if}
<div id="containerX">
    <div id="loading-box"></div>
    <div id="top-wrapper">
      <div class="viewheadertop">
        <div class="fr links">
          {if $microheaderlinks}
            {foreach from=$microheaderlinks item=item}
              <a class="btn" href="{$item.url}">{$item.name}</a>
            {/foreach}
          {/if}
          <a class="btn nojs-hidden-inline" href="javascript:history.back()">{str tag=back}</a>
        </div>
        <div class="fl">
          <a class="small-logo" href="{$WWWROOT}"><img src="{theme_url filename='images/site-logo-small.png'}" alt="{$sitename}"></a>
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
            <a href="{$WWWROOT}admin/">{str tag="administration"}</a> |
          {elseif $USER->is_institutional_admin()}
            <a href="{$WWWROOT}admin/users/search.php">{str tag="administration"}</a> |
          {/if}

          {if $mnethost}<a href="{$mnethost.url}">{str tag=backto arg1=$mnethost.name}</a>&nbsp;{/if}
          <a href="{$WWWROOT}?logout">{str tag="logout"}</a>
        </div>
       </div>
{/if}
      </div>
      <div class="viewheader">

{if $collection}
        <div id="collection"><h1 class="collection-title">{$microheadertitle|safe}</h1>{include file=collectionnav.tpl}</div>
{else}
        <h1 class="center title">{$microheadertitle|safe}</h1>
{/if}
		<div class="cb"></div>
      </div>
    </div>
	<div class="cb"></div>
  <div id="mainmiddlewrap">
    <div id="mainmiddle">
      <div id="main-wrapper">
          <div class="main-column">
              <div id="main-column-container">
              {dynamic}{insert_messages}{/dynamic}
