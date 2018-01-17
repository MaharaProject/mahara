{foreach from=$data item=item key=key}
  <tr class="{cycle values='r0,r1'}">
    {if $columns.rownum}<td>{$offset + $dwoo.foreach.default.iteration}</td>{/if}
    {if $columns.viewname}<td><a href="{$WWWROOT}view/view.php?id={$item->viewid}">{$item->viewname}</a></td>{/if}
    {if $columns.artefactname}<td>{$item->artefactname}</td>{/if}
    {if $columns.reporter}<td><a href="{$WWWROOT}user/view.php?id={$item->reportedby}">{$item->reporter}</a></td>{/if}
    {if $columns.report}<td>{$item->report}</td>{/if}
    {if $columns.date}<td>{format_date(strtotime($item->reportedtime))}</td>{/if}
    {if $columns.reviewer}<td><a href="{$WWWROOT}user/view.php?id={$item->reviewedby}">{$item->reviewer}</a></td>{/if}
    {if $columns.review}<td>{$item->review}</td>{/if}
    {if $columns.reviewdate}<td>{format_date(strtotime($item->reviewedtime))}</td>{/if}
    {if $columns.status}<td class="center">{str tag='status.$item->status' section='statistics'}</td>{/if}
  </tr>
{/foreach}

