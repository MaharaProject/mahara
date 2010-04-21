{foreach from=$data item=item key=key}
  <tr class="{cycle values='r0,r1'}">
    <td>{$offset + $dwoo.foreach.default.iteration}</td>
    <td><a href="{$WWWROOT}group/view.php?id={$item->id}">{$item->name}</a></td>
    <td class="center">{$item->members}</td>
    <td class="center">{$item->views}</td>
    <td class="center">{$item->forums}</td>
    <td class="center">{$item->posts}</td>
  </tr>
{/foreach}
