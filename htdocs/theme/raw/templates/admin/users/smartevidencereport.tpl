{foreach from=$data item=item}
  <tr class="{cycle values='r0,r1'}">
    {if $columns.rownum}<td>{$offset + $dwoo.foreach.default.iteration}</td>{/if}
    {if $columns.firstname}<td><a href="{$item->userurl}">{$item->firstname}</a></td>{/if}
    {if $columns.lastname}<td><a href="{$item->userurl}">{$item->lastname}</a></td>{/if}
    {if $columns.displayname}<td><a href="{$item->userurl}">{$item->displayname}</a></td>{/if}
    {if $columns.username}<td><a href="{$item->userurl}">{$item->username}</a></td>{/if}
    {if $columns.id}<td>{$item->viewid}</td>{/if}
    {if $columns.email}<td>{$item->email}</td>{/if}
    <td>
    {if $item->canbeviewed}
        <a href="{$item->collectionurl}">{$item->title}</a>
    {else}
        {$item->title}
    {/if}
    </td>
    {if $columns.pagecount}<td>{$item->views}</td>{/if}
    {if $columns.numviews}<td>{$item->views}</td>{/if}
    {if $columns.submittedstatus}<td>{$item->submittedstatus}</td>{/if}
    {if $columns.accessrules}<td>{include file="admin/users/smartevidencereportaccessitem.tpl" item=$item}</td>{/if}
    {if $columns.evidence_begun}<td>{$item->evidence_begun}</td>{/if}
    {if $columns.evidence_incomplete}<td>{$item->evidence_incomplete}</td>{/if}
    {if $columns.evidence_partialcomplete}<td>{$item->evidence_partialcomplete}</td>{/if}
    {if $columns.evidence_completed}<td>{$item->evidence_completed}</td>{/if}
  </tr>
{/foreach}
