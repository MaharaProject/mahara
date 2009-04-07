<table>
<colgroup width="50%" span="2"></colgroup>
{foreach from=$fields key='field' item='value'}
    <tr>
        <td>{$field}</td>
        <td>{$value}</td>
    </tr>
{/foreach}
</table>
