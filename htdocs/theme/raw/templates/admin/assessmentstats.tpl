{foreach from=$data item=item key=key}
  <tr class="{cycle values='r0,r1'}">
      {if $columns.rownum}<td>{$offset + $dwoo.foreach.default.iteration}</td>{/if}
      {if $columns.type}<td>{$item->type}</td>{/if}
      {if $columns.viewname}<td>{$item->viewname}</td>{/if}
      {if $columns.owner}<td><a href="{$WWWROOT}user/view.php?id={$item->userid}">{$item->owner}</a></td>{/if}
      {if $columns.group}<td>{$item->groupname}</td>{/if}
      {if $columns.submitted}<td>{format_date(strtotime($item->submitted))}</td>{/if}
      {if $columns.released}<td>{if $item->released}{format_date(strtotime($item->released))}{/if}</td>{/if}
      {if $columns.marker}<td>{if $item->markerid}<a href="{$WWWROOT}user/view.php?id={$item->markerid}">{$item->marker}</a> {/if}</td>{/if}
  </tr>
{/foreach}