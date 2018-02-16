{foreach from=$data item=item}
  <tr class="{cycle values='r0,r1'}">
    {if $columns.rownum}<td>{$offset + $dwoo.foreach.default.iteration}</td>{/if}
    {if $columns.firstname}<td><a href="{$item->profileurl}">{$item->firstname}</a></td>{/if}
    {if $columns.lastname}<td><a href="{$item->profileurl}">{$item->lastname}</a></td>{/if}
    {if $columns.displayname}<td><a href="{$item->profileurl}">{$item->displayname}</a></td>{/if}
    {if $columns.email}<td>{$item->email}</td>{/if}
    {if $columns.username}<td><a href="{$item->profileurl}">{$item->username}</a></td>{/if}
    {if $columns.siteprivacy}<td><a href="{$WWWROOT}admin/users/statisticsuseragreements.php?userid={$item->id}&activetab=privacy&versionid={$item->siteprivacyid}">{$item->siteprivacy}</a></td>{/if}
    {if $columns.siteprivacyconsentdate}<td>{$item->siteprivacyconsentdate}</td>{/if}
    {if $columns.siteterms}<td><a href="{$WWWROOT}admin/users/statisticsuseragreements.php?userid={$item->id}&activetab=termsandconditions&versionid={$item->sitetermsid}">{$item->siteterms}</a></td>{/if}
    {if $columns.sitetermsconsentdate}<td>{$item->sitetermsconsentdate}</td>{/if}
    {if $columns.institutionprivacy}<td><a href="{$WWWROOT}admin/users/statisticsuseragreements.php?userid={$item->id}&activetab=privacy&versionid={$item->institutionprivacyid}">{$item->institutionprivacy}</a></td>{/if}
    {if $columns.institutionprivacyconsentdate}<td>{$item->institutionprivacyconsentdate}</td>{/if}
    {if $columns.institutionterms}<td><a href="{$WWWROOT}admin/users/statisticsuseragreements.php?userid={$item->id}&activetab=termsandconditions&versionid={$item->institutiontermsid}">{$item->institutionterms}</a></td>{/if}
    {if $columns.institutiontermsconsentdate}<td>{$item->institutiontermsconsentdate}</td>{/if}
    {if $columns.institution}<td>{$item->instname}</td>{/if}
  </tr>
{/foreach}