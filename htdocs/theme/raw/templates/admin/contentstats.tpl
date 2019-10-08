{foreach from=$data item=item key=key}
  <tr class="{cycle values='r0,r1'}">
    {if $columns.rownum}<td>{$offset + $dwoo.foreach.default.iteration}</td>{/if}
    {if $columns.name}<td>{str tag=$item->field section=statistics}</td>{/if}
    {if $columns.modified}<td>{$item->modified}</td>{/if}
    {if $columns.total}<td class="center">{$item->value}</td>{/if}
  </tr>
{/foreach}

