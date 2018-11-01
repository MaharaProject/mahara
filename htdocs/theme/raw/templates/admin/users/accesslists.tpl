{foreach from=$data item=item}
  <tr class="{cycle values='r0,r1'} {if $item->pending}bg-danger{/if}">
    {if $columns.rownum}<td>{$offset + $dwoo.foreach.default.iteration}</td>{/if}
    {if $columns.owner}<td><a href="{$item->userurl}">{$item->displayname}</a></td>{/if}
    {if $columns.views}<td>
        {if $item->views > 0}{if $item->pending}<div class="detail text-danger"><strong>{str tag="pending" section="view"}</strong></div>{/if}<a href="{$WWWROOT}view/view.php?id={$item->viewid}">{/if}
        {$item->title}
        {if $item->views > 0}</a>{/if}
        </td>{/if}
    {if $columns.numviews}<td>{$item->views}</td>{/if}
    {if $columns.accessrules}<td>{include file="admin/users/accesslistitem.tpl" item=$item}</td>{/if}
  </tr>
{/foreach}
