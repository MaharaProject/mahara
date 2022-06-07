<label class="accessible-hidden sr-only" for="selectusers_{$r.id}">{str tag=selectuser section=admin arg1="$r.firstname $r.lastname"}</label>
<input name="selectusers" class="selectusers" type="checkbox" id="selectusers_{$r.id}" value="{$r.id}">
{if $r.canedituser}
<a class="search-masquerade" href="{$WWWROOT}admin/users/changeuser.php?id={$r.id}">
    <span class="icon text-default icon-user-secret left" role="presentation" aria-hidden="true"></span>
</a>
{/if}