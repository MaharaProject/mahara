{include file="header.tpl"}

<div class="content">
<div class="box-cnrs"><span class="cnr-tl"><span class="cnr-tr"><span 
class="cnr-bl"><span class="cnr-br">
	<div class="maincontent">
	
<h2>{$TITLE}</h2>

{if $VIEWCONTENT}
   {$VIEWCONTENT}
{else}
   {str tag=viewviewnotallowed}
{/if}

	</div>
</span></span></span></span></div>	
</div>

{include file="footer.tpl"}
