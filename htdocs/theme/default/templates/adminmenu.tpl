<div class="sidebar">
{str tag="mainmenu"}
{if $site_menu}
    <ul id="sitemenu">
{foreach from=$site_menu item=item}
    	<li class="{cycle values=r0,r1}"><a href="{$item.link|escape}">{$item.name}</a></li>
{/foreach}
    </ul>
{/if}
	<div class="loginbox-botcorners"><img src="{image_path imagelocation='images/sidebox_bot.gif'}" border="0" alt=""></div>
</div>
