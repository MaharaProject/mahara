{foreach from=$data item=item}
  <tr class="{cycle values='r0,r1'}">
    {if $columns.rownum}<td>{$offset + $dwoo.foreach.default.iteration}</td>{/if}
    {if $columns.date}<td>{$item.date}</td>{/if}
    {if $columns.loggedin}<td class="center">{$item.loggedin}</td>{/if}
    {if $columns.created}<td class="center">{$item.created}</td>{/if}
    {if $columns.total}<td class="center">{$item.total}</td>{/if}
  </tr>
{/foreach}
