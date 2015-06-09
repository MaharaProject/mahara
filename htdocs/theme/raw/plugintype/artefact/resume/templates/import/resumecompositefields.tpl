<table class="resumepersonalinfo fullwidth">
{foreach from=$fields key='field' item='value'}
    {if $value}
    <tr>
        <th class="onethirdwidth">{$field}</th>
        <td>{$value}</td>
    </tr>
    {/if}
{/foreach}
</table>
{if $license}
<div class="resumelicense">
{$license|safe}
</div>
{/if}
