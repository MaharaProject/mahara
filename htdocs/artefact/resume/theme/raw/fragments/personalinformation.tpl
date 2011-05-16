<table class="resumepersonalinfo">
{foreach from=$fields key='field' item='value'}
    {if $value}
    <tr>
        <th>{$field}</th>
        <td>{$value}</td>
    </tr>
    {/if}
{/foreach}
</table>
