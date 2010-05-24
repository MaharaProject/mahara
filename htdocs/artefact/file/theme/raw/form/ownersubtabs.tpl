{if $tabs.subtabs}
<ul class="artefactchooser-subtabs">
  {foreach from=$tabs.subtabs item=displayname key=ownerid}
  <li{if $tabs.ownerid == $ownerid} class="current"{/if}><a class="changeowner" href="{$querybase}owner={$tabs.owner}&ownerid={$ownerid}">{$displayname}</a></li>
  {/foreach}
</ul>
{/if}
