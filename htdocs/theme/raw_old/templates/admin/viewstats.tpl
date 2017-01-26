{foreach from=$data item=item key=key}
  <tr class="{cycle values='r0,r1'}">
    <td>{$offset + $dwoo.foreach.default.iteration}</td>
    <td><a href="{$item->fullurl}">{$item->title}</a></td>
    <td>{if $item->ownerurl}<a href="{$item->ownerurl}">{/if}{$item->ownername}{if $item->ownerurl}</a>{/if}</td>
    <td class="right">{$item->visits}</td>
    <td class="center">{$item->comments}</td>
  </tr>
{/foreach}

