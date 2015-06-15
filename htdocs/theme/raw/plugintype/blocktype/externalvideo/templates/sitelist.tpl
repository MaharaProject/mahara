<ul class="text-inline unstyled">
{foreach from=$data item=item}
  <li><a href="{$item.url}" target="_blank"><img src="{$item.icon}" alt="{$item.name}" title="{$item.name}"></a></li>
{/foreach}
</ul>
