{include file="header.tpl"}

<div id="column-right">
	<div class="sidebarforgotpassword"><a href="{$WWWROOT}forgotpass.php">{str tag=forgotpassword}</a></div>
	<div class="sidebarforgotpassword-botcorners"><img src="{image_path imagelocation='images/sidebox_bot.gif'}" border="0" alt=""></div>

{include file="adminmenu.tpl"}
</div>

{include file="columnleftstart.tpl"}
			<h2>{str tag=register}</h2>
			
			{if $register_form}
			<p>{str tag=registerdescription}</P>
			
			{$register_form}
			{elseif $register_profile_form}
			{$register_profile_form}
			{/if}
{include file="columnleftend.tpl"}
{include file="footer.tpl"}
