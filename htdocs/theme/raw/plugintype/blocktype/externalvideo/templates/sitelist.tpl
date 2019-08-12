<ul class="text-inline unstyled">
{foreach from=$data item=item}
  <li class="text-inline"><a href="{$item.url}">
  {if $item.faicon}
  <span class="icon icon-lg icon-{$item.faicon} icon-brand" style="{$item.style}"></span>
  {else}
  <img src="{$item.icon}" alt="{$item.name}" title="{$item.name}">
  {/if}</a></li>
{/foreach}
</ul>