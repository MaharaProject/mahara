{if $data}
    {foreach from=$data item=institution}
        <tr>
            <td><a href="{$WWWROOT}institution/index.php?institution={$institution->name}">{$institution->displayname}</a></td>
            <td>{$institution->count_logins}</td>
            <td>{$institution->count_active}</td>
        </tr>
    {/foreach}
{/if}
