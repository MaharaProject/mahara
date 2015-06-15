{foreach from=$data item=item key=key}
  <tr class="{cycle values='r0,r1'}">
    <td>{$offset + $dwoo.foreach.default.iteration}</td>
    <td><a href="statistics.php?{if $institution}institution={$institution}&{/if}type=historical&field={$item->field}">{str tag=$item->field section=statistics}</a></td>
    <td>{$item->modified}</td>
    <td class="center">{$item->value}</td>
  </tr>
{/foreach}

