{foreach from=$data item=item key=key}
  <tr class="{cycle values='r0,r1'}">
    {if $columns.rownum}<td>{$offset + $dwoo.foreach.default.iteration}</td>{/if}
    {if $columns.institution}<td><a href="{$WWWROOT}institution/index.php?institution={$item->name}">{$item->displayname}</a></td>{/if}
    {if $columns.members}<td>{$item->count_members}</td>{/if}
    {if $columns.views}<td>{$item->count_views}</td>{/if}
    {if $columns.blocks}<td>{$item->count_blocks}</td>{/if}
    {if $columns.artefacts}<td>{$item->count_artefacts}</td>{/if}
    {if $columns.posts}<td>{$item->count_interaction_forum_post}</td>{/if}
  </tr>
{/foreach}

