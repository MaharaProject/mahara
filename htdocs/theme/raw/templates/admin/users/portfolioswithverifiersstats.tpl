{foreach from=$data item=item}
  <tr class="{cycle values='r0,r1'}">
    {if $columns.rownum}<td>{$offset + $dwoo.foreach.default.iteration}</td>{/if}
    {if $columns.date}<td>{$item->date}</td>{/if}
    {if $columns.hasverifier}<td>{$item->hasverifier}</td>{/if}
    {if $columns.noverifier}<td>{$item->noverifier}</td>{/if}
  </tr>
{/foreach}
