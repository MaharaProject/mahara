{foreach from=$data item=item key=key}
  <tr class="{cycle values='r0,r1'}">
    <td>{$offset + $dwoo.foreach.default.iteration}</td>
    <td><a href="{$item->fullurl}">{$item->title} <span class="accessible-hidden">{str tag=visitedtimesrank section=admin arg1=$item->visits arg2=$offset + $dwoo.foreach.default.iteration}</span></a></td>
    <td>{if $item->ownerurl}<a href="{$item->ownerurl}"><span class="accessible-hidden">{str tag=pageownedby section=admin} </span>{/if}{$item->ownername}{if $item->ownerurl}</a>{/if}</td>
    <td class="right">{$item->visits}</td>
    <td class="center">{$item->comments}</td>
  </tr>
{/foreach}

