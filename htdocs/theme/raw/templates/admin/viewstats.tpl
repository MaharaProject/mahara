{foreach from=$data item=item key=key}
  <tr class="{cycle values='r0,r1'}">
    <td>{$offset + $dwoo.foreach.default.iteration}</td>
    <td><a href="{$WWWROOT}view/view.php?id={$item->id}">{$item->title}</a></td>
    <td>{$item->author}</td>
    <td>{$item->visits}</td>
    <td>{$item->comments}</td>
  </tr>
{/foreach}
