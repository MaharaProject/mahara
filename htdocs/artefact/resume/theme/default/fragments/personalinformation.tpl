<table>
<colgroup width="50%" span="2"></colgroup>
{foreach from=$fields key='field' item='value'}
    <tr>
        <td>{$field|escape}</td>
        <td>{$value|escape}</td>
    </tr>
{/foreach}
</table>
