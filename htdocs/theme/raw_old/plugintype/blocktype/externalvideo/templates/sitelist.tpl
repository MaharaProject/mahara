<ul class="text-inline unstyled">
{foreach from=$data item=item}
  <li class="text-inline"><a href="{$item.url}"><img src="{$item.icon}" alt="{$item.name}" title="{$item.name}"></a></li>
{/foreach}
</ul>