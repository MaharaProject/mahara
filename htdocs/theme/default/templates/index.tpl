{include file="header.tpl"}

{if !$LOGGEDIN}
<div class="sidebar" id="loginbox">
<noscript><p>{str tag="javascriptnotenabled"}</p></noscript>

{$login_form}
	<div class="sidebar-botcorners"><img src="{image_path imagelocation='images/sidebox_bot.gif'}" border="0"></div>
</div>
{else}
{include file="searchbox.tpl"}
{/if}

{include file="adminmenu.tpl"}

<div class="content">
<div class="box-cnrs"><span class="cnr-tl"><span class="cnr-tr"><span 
class="cnr-bl"><span class="cnr-br">
	<div class="maincontent">
    {$page_content}
	</div>
</span></span></span></span></div>	
</div>

{include file="footer.tpl"}
