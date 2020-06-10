{foreach from=$data item=item}
  <tr class="{cycle values='r0,r1'}">
    <td>{$item.process}</td>
    <td class="center">{$item.starttime}</td>
  </tr>
{/foreach}
