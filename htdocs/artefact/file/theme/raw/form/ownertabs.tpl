<ul class="artefactchooser-tabs files">
  {foreach from=$tabs.tabs item=displayname key=name}
  <li{if $tabs.owner == $name} class="current"{/if}><a class="changeowner" href="{$querybase}owner={$name}">{$displayname}</a></li>
  {/foreach}
</ul>
