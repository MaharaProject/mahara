{foreach from=$data item=item}
  <tr class="{cycle values='r0,r1'}">
    {if $columns.rownum}<td>{$offset + $dwoo.foreach.default.iteration}</td>{/if}
    {if $columns.firstname}<td><a href="{$item->profileurl}">{$item->firstname}</a></td>{/if}
    {if $columns.lastname}<td><a href="{$item->profileurl}">{$item->lastname}</a></td>{/if}
    {if $columns.email}<td>{$item->email}</td>{/if}
    {if $columns.studentid}<td>{$item->studentid}</td>{/if}
    {if $columns.displayname}<td><a href="{$item->profileurl}">{$item->displayname}</a></td>{/if}
    {if $columns.username}<td><a href="{$item->profileurl}">{$item->username}</a></td>{/if}
    {if $columns.remotename}<td>{$item->remotename}</td>{/if}
    {if $columns.quotapercent}<td>{$item->quotapercent_format}% <span class="text-small text-midtone">({$item->quotaused_format}/{$item->quota_format})</span></td>{/if}
    {if $columns.lastlogin}<td>{$item->lastlogin}</td>{/if}
    {if $columns.probation}<td>{$item->probation}</td>{/if}
  </tr>
{/foreach}
