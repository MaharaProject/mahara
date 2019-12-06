{if $r.userroles}
    {foreach from=$r.userroles item=role}
    <div>{str tag=$role}</div>
    {/foreach}
{/if}