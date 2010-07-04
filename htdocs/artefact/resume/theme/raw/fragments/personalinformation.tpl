<table class="resumepersonalinfo">
<colgroup width="50%" span="2"></colgroup>
{foreach from=$fields key='field' item='value'}
    <tr>
        <th>{$field}</th>
        <td>{$value}</td>
    </tr>
{/foreach}
</table>
