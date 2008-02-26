<div id="column-left">
	<div class="maincontent">
	{insert name="messages"}

	{if $PAGEHELPNAME && $heading} <h2>{$heading|escape}<span id="{$PAGEHELPNAME}_container" class="pagehelpicon">{$PAGEHELPICON}</span></h2>{/if}
