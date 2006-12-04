{include file="header.tpl"}

<div class="content">
<div class="box-cnrs"><span class="cnr-tl"><span class="cnr-tr"><span 
class="cnr-bl"><span class="cnr-br">
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

{include file="footer.tpl"}
