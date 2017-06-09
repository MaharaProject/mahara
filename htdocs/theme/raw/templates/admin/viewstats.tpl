{foreach from=$data item=item key=key}
  <tr class="{cycle values='r0,r1'}">
    {if $columns.rownum}<td>{$offset + $dwoo.foreach.default.iteration}</td>{/if}
    {if $columns.view}<td><a href="{$item->fullurl}">{$item->title}</a></td>{/if}
    {if $columns.collection}<td><a href="{$item->fullurl}">{$item->name}</a></td>{/if}
    {if $columns.owner}<td>{if $item->ownerurl}<a href="{$item->ownerurl}">{/if}{$item->ownername}{if $item->ownerurl}</a>{/if}</td>{/if}
    {if $columns.created}<td>{format_date(strtotime($item->ctime))}</td>{/if}
    {if $columns.modified}<td>{format_date(strtotime($item->mtime))}</td>{/if}
    {if $columns.visited}<td>{format_date(strtotime($item->atime))}</td>{/if}
    {if $columns.blocks}<td>{$item->blocks}</td>{/if}
    {if $columns.visits}<td class="right">{$item->visits}</td>{/if}
    {if $columns.comments}<td class="center">{$item->comments}</td>{/if}
  </tr>
{/foreach}

