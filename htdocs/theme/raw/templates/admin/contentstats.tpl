{foreach from=$data item=item key=key}
  <tr class="{cycle values='r0,r1'}">
    {if $columns.rownum}<td>{$offset + $dwoo.foreach.default.iteration}</td>{/if}
    {if $columns.name}<td><a href="statistics.php?{if $institution}institution={$institution}&{/if}type=content&subtype=historical&field={$item->field}">{str tag=$item->field section=statistics}</a></td>{/if}
    {if $columns.modified}<td>{$item->modified}</td>{/if}
    {if $columns.total}<td class="center">{$item->value}</td>{/if}
  </tr>
{/foreach}

