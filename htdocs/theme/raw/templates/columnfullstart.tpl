{auto_escape off}
<div id="column-full">
	<div class="maincontent">
	{insert name="messages"}
	
    {if $PAGEHELPNAME && $heading && $noheadingescape} <h2>{$heading}<span id="{$PAGEHELPNAME}_container" class="pagehelpicon">{$PAGEHELPICON}</span></h2>
    {elseif $PAGEHELPNAME && $heading} <h2>{$heading|escape}<span id="{$PAGEHELPNAME}_container" class="pagehelpicon">{$PAGEHELPICON}</span></h2>{/if}
{/auto_escape}
