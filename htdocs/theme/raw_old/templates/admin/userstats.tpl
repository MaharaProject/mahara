{foreach from=$data item=item}
  <tr class="{cycle values='r0,r1'}">
    <td>{$item.date}</td>
    <td class="center">{$item.loggedin}</td>
    <td class="center">{$item.created}</td>
    <td class="center">{$item.total}</td>
  </tr>
{/foreach}
