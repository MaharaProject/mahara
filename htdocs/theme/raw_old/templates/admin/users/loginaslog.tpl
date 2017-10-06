{foreach from=$data item=item}
  <tr class="{cycle values='r0,r1'}">
    {if $columns.rownum}<td>{$offset + $dwoo.foreach.default.iteration}</td>{/if}
    {if $columns.user}<td><a href="{$item->userurl}">{$item->user}</a></td>{/if}
    {if $columns.reason}<td>{$item->reason}</td>{/if}
    {if $columns.masquerader}<td><a href="{$item->masqueraderurl}">{$item->masquerader}</a></td>{/if}
    {if $columns.date}<td>{$item->date}</td>{/if}
  </tr>
{/foreach}
