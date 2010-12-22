<table class="resumepersonalinfo">
{foreach from=$fields key='field' item='value'}
    <tr>
        <th>{$field}</th>
        <td>{$value}</td>
    </tr>
{/foreach}
</table>
