{if $data}
  <ul class="text-inline unstyled">
  {foreach from=$data item=item}
    <li class="text-inline"><a href="{$item.url}" target="_blank"><img src="{$item.icon}" alt="{$item.name}" title="{$item.name}"></a></li>
  {/foreach}
  </ul>
{else}
  {$nodata|safe}
{/if}