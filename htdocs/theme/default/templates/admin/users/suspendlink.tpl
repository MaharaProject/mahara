{if !$r.suspended || $r.suspended == 'f'}
<a class="suspend-user-link" href="{$WWWROOT}admin/users/suspend.php?id={$r.id}">{str tag=suspenduser section=admin}</a>
{/if}