{foreach from=$data item=item key=key}
  <tr class="{cycle values='r0,r1'}">
    <td>{$offset + $dwoo.foreach.default.iteration}</td>
    <td><a href="{$WWWROOT}group/view.php?id={$item->id}">{$item->name}</a></td>
    <td>{$item->members}</td>
    <td>{$item->views}</td>
    <td>{$item->forums}</td>
    <td>{$item->posts}</td>
  </tr>
{/foreach}
