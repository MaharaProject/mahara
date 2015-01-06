{if $tabs.subtabs}
<ul class="artefactchooser-subtabs">
  {foreach from=$tabs.subtabs item=displayname key=ownerid name=subtab}
  <li class="{if $tabs.ownerid == $ownerid}current{/if}{if !$dwoo.foreach.subtab.last} showrightborder{/if}"><a class="changeowner" href="{$querybase}owner={$tabs.owner}&ownerid={$ownerid}">{$displayname}</a></li>
  {/foreach}
</ul>
{/if}
