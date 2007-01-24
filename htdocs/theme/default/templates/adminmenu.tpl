{counter name="sidebar" assign=SIDEBAR_SEQUENCE}
{if $SIDEBAR_SEQUENCE > 3}{assign var=SIDEBAR_SEQUENCE value=3}{/if}
<div class="sidebar sidebar_{$SIDEBAR_SEQUENCE}">
    <h3>{str tag="mainmenu"}</h3>
{if $SITEMENU}
    <ul id="sitemenu">
{foreach from=$SITEMENU item=item}
    	<li class="{cycle values=r0,r1}"><a href="{$item.link|escape}">{$item.name}</a></li>
{/foreach}
    </ul>
{/if}
	<div class="loginbox-botcorners"><img src="{theme_path location='images/sidebox_bot.gif'}" border="0" alt=""></div>
</div>
