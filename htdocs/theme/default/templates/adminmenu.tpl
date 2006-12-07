<div class="sidebar">
{str tag="mainmenu"}
    <ul id="sitemenu">
		<li class="r0"> testing</li>
		<li class="r1"> testing</li>
		<li class="r0"> testing</li>
		<li class="r1"> testing</li>
{foreach from=$site_menu item=item}
    	<li class="{cycle values=r0,r1}"><a href="{$item.link|escape}">{$item.name}</a></li>
{/foreach}
    </ul>
	<div class="loginbox-botcorners"><img src="{image_path imagelocation='images/sidebox_bot.gif'}" border="0"></div>
</div>
