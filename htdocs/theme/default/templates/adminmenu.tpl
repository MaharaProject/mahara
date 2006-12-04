<div class="sidebar">
{str tag="mainmenu"}
    <ul id="sitemenu">
{foreach from=$site_menu item=item}
    <li><a href="{$item.link|escape}">{$item.name}</a></li>
{/foreach}
    </ul>
	<div class="sidebar-botcorners"><img src="{image_path imagelocation='images/sidebox_bot.gif'}" border="0"></div>
</div>
