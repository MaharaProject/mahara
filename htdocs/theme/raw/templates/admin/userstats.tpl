{foreach from=$data item=item}
  <tr class="{cycle values='r0,r1'}">
    <td>{$item.date}</td>
    <td>{$item.loggedin}</td>
    <td>{$item.created}</td>
    <td>{$item.total}</td>
  </tr>
{/foreach}

