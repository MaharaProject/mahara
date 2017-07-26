{foreach from=$data item=item}
  <tr class="{cycle values='r0,r1'}">
    {if $columns.rownum}<td>{$offset + $dwoo.foreach.default.iteration}</td>{/if}
    {if $columns.date}<td>{$item->date}</td>{/if}
    {if $columns.comments}<td>{$item->comments}</td>{/if}
    {if $columns.annotations}<td>{$item->annotations}</td>{/if}
    {if $columns.usershare}<td>{$item->usershare}</td>{/if}
    {if $columns.groupshare}<td>{$item->groupshare}</td>{/if}
    {if $columns.institutionshare}<td>{$item->institutionshare}</td>{/if}
    {if $columns.loggedinshare}<td>{$item->loggedinshare}</td>{/if}
    {if $columns.publicshare}<td>{$item->publicshare}</td>{/if}
    {if $columns.secretshare}<td>{$item->secretshare}</td>{/if}
    {if $columns.friendshare}<td>{$item->friendshare}</td>{/if}
  </tr>
{/foreach}
