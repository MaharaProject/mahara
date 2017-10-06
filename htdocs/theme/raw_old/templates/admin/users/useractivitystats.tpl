{foreach from=$data item=item}
  <tr class="{cycle values='r0,r1'}">
    {if $columns.rownum}<td>{$offset + $dwoo.foreach.default.iteration}</td>{/if}
    {if $columns.firstname}<td><a href="{$item->profileurl}">{$item->firstname}</a></td>{/if}  
    {if $columns.lastname}<td><a href="{$item->profileurl}">{$item->lastname}</a></td>{/if}
    {if $columns.displayname}<td><a href="{$item->profileurl}">{$item->displayname}</a></td>{/if}
    {if $columns.username}<td><a href="{$item->profileurl}">{$item->username}</a></td>{/if}
    {if $columns.artefacts}<td>{$item->artefacts}</td>{/if}
    {if $columns.pages}<td>{$item->pages}</td>{/if}
    {if $columns.collections}<td>{$item->collections}</td>{/if}
    {if $columns.groups}<td>{$item->groups}</td>{/if}
    {if $columns.logins}<td>{$item->logins}</td>{/if}
    {if $columns.actions}<td>{$item->actions}</td>{/if}
    {if $columns.lastlogin}<td>{$item->lastlogin}</td>{/if}
    {if $columns.lastactivity}<td>{$item->lastactivity}</td>{/if}
  </tr>
{/foreach}
