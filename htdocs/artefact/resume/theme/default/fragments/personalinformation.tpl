<table>
{foreach from=$fields key='field' item='value'}
    <tr>
        <td>{$field|escape}</td>
        <td>{$value|escape}</td>
    </tr>
{/foreach}
</table>
