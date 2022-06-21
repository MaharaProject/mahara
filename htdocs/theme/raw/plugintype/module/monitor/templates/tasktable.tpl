{foreach from=$data item=item}
    <tr class="{cycle values='r0,r1'}">
        <td>{$item.task}</td>
        <td class="center">{$item.value}</td>
    </tr>
{/foreach}