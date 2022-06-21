{foreach from=$data item=item}
  <tr class="{cycle values='r0,r1'}">
    {if $columns.rownum}<td>{$offset + $dwoo.foreach.default.iteration}</td>{/if}
    {if $columns.date}<td>w.c. {$item->date}</td>{/if}
    {if $columns.one}<td>{$item->one}</td>{/if}
    {if $columns.two}<td>{$item->two}</td>{/if}
    {if $columns.three}<td>{$item->three}</td>{/if}
    {if $columns.four}<td>{$item->four}</td>{/if}
    {if $columns.five}<td>{$item->five}</td>{/if}
    {if $columns.six}<td>{$item->six}</td>{/if}
    {if $columns.seven}<td>{$item->seven}</td>{/if}
    {if $columns.eight}<td>{$item->eight}</td>{/if}
    {if $columns.nine}<td>{$item->nine}</td>{/if}
    {if $columns.tenormore}<td>{$item->tenormore}</td>{/if}
  </tr>
{/foreach}
