{auto_escape off}
<table>
    <tbody>
    {foreach from=$PROPERTIES item=item}
        <tr>
            <td>{$item.name}</td>
            <td>{$item.value}</td>
        </tr>
    {/foreach}
    </tbody>
</table>
{/auto_escape}
