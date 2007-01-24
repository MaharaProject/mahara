{include file="header.tpl"}

<div id="column-right">
{if !$LOGGEDIN}
	<div id="loginbox">
	<noscript><p>{str tag="javascriptnotenabled"}</p></noscript>
	{$login_form}
		<div class="sidebar-botcorners"><img src="{theme_path location='images/sidebox_bot.gif'}" border="0" alt=""></div>
	</div>
{/if}
{include file="adminmenu.tpl"}
</div>

{include file="columnleftstart.tpl"}
{$page_content}
{include file="columnleftend.tpl"}

{include file="footer.tpl"}
