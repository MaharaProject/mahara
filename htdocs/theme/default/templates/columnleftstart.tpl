<div id="column-left">
	<div class="maincontent{if $GROUP} group{/if}">
	{insert name="messages"}

	{if $heading}<h2>{$heading|escape}{if $PAGEHELPNAME}<span id="{$PAGEHELPNAME}_container" class="pagehelpicon">{$PAGEHELPICON}</span>{/if}</h2>{/if}

{if $GROUP}
{* Tabs and beginning of page container for group info pages *}
                <div id="grouppage-tabs">
                <ul>
                {foreach from=$GROUPNAV item=item}
                    <li{if $item.selected} class="selected"{/if}><a href="{$WWWROOT}{$item.url|escape}">{$item.title|escape}</a></li>
                {/foreach}
                </ul>
                </div>
                <div id="grouppage-container">
{/if}

