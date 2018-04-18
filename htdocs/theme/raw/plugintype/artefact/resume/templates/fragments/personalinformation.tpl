<div class="card-body flush">
    <table class="table resumepersonalinfo profile-info">
    {foreach from=$fields key='field' item='value'}
        {if $value}
        <tr>
            <th>{$field}</th>
            <td>{$value}</td>
        </tr>
        {/if}
    {/foreach}
    </table>
    {if $license}
    <div class="license">
    {$license|safe}
    </div>
    {/if}
</div>
