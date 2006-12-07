{include file="header.tpl"}

<div id="column-right">
	<div class="sidebarforgotpassword"><a href="{$WWWROOT}forgotpass.php">{str tag=forgotpassword}</a></div>
	<div class="sidebarforgotpassword-botcorners"><img src="{image_path imagelocation='images/sidebox_bot.gif'}" border="0"></div>

{include file="adminmenu.tpl"}
</div>

<div id="column-left">
	<div class="content">
		<div class="box-cnrs"><span class="cnr-tl"><span class="cnr-tr"><span class="cnr-bl"><span class="cnr-br">
			<div class="maincontent">
			<h2>{str tag=register}</h2>
			
			{if $register_form}
			<p>{str tag=registerdescription}</P>
			
			{$register_form}
			{elseif $register_profile_form}
			{$register_profile_form}
			{/if}
			
			</div>
		</span></span></span></span></div>	
	</div>
</div>

{include file="footer.tpl"}
