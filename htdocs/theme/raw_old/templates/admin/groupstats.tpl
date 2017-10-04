{foreach from=$data item=item key=key}
  <tr class="{cycle values='r0,r1'}">
    {if $columns.rownum}<td>{$offset + $dwoo.foreach.default.iteration}</td>{/if}
    {if $columns.id}<td>{$item->id}</td>{/if}
    {if $columns.group}<td><a href="{$item->homeurl}">{$item->name}</a></td>{/if}
    {if $columns.members}<td class="center">{$item->members}</td>{/if}
    {if $columns.views}<td class="center">{$item->views}</td>{/if}
    {if $columns.groupcomments}<td class="center">{$item->groupcomments}</td>{/if}
    {if $columns.sharedviews}<td class="center">{$item->sharedviews}</td>{/if}
    {if $columns.sharedcomments}<td class="center">{$item->sharedcomments}</td>{/if}
    {if $columns.forums}<td class="center">{$item->forums}</td>{/if}
    {if $columns.posts}<td class="center">{$item->posts}</td>{/if}
  </tr>
{/foreach}
