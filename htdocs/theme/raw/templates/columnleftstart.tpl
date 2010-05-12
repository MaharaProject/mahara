{auto_escape off}
<div id="column-left">
	<div class="maincontent{if $GROUP} group{/if}">
	{insert name="messages"}

	{if $heading}<h2>{$heading|escape}{if $PAGEHELPNAME}<span id="{$PAGEHELPNAME}_container" class="pagehelpicon">{$PAGEHELPICON}</span>{/if}</h2>{/if}

{if $GROUP}
{* Tabs and beginning of page container for group info pages *}
                <ul class="in-page-tabs">
                {foreach from=$GROUPNAV item=item}
                    <li><a {if $item.selected}class="current-tab" {/if}href="{$WWWROOT}{$item.url|escape}">{$item.title|escape}</a></li>
                {/foreach}
                </ul>
                <div id="grouppage-container">
{/if}

{/auto_escape}
