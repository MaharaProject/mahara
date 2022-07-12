{if $r.canedituser}
<button class="btn btn-secondary btn-sm" data-url="{$WWWROOT}admin/users/changeuser.php?id={$r.id}" title='{str tag=masqueradeasperson section=admin arg1=$r.firstname arg2=$r.lastname}'>
    <span class="icon text-default icon-user-secret" role="presentation" aria-hidden="true"></span>
</button>
{/if}
