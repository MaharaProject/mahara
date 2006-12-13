{include file="header.tpl"}

<div id="column-full">		
	<div class="{$type}"{if $type == 'error'} style="color:#dd0221;"{else}style="color:#547c22;"{/if}>
		<div class="box-cnrs"><span class="cnr-tl"><span class="cnr-tr"><span class="cnr-bl"><span class="cnr-br">
			<div class="maincontent">
			{$message}
			</div>
		</span></span></span></span></div>	
	</div>
</div>

{include file="footer.tpl"}
