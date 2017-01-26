{foreach from=$data item=item key=key}
  <tr class="{cycle values='r0,r1'}">
    <td>{$item->time}</td>
    <td class="center">{$item->modified}</td>
    <td class="center">{$item->value}</td>
  </tr>
{/foreach}

