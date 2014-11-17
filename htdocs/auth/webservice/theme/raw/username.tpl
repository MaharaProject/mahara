{if $USER->get('admin')}
<a href="{$WWWROOT}admin/users/edit.php?id={$r.userid}" title="{$r.firstname} {$r.lastname} ({$r.email})">{$r.username}</a>
{else}
<a href="{$WWWROOT}user/view.php?id={$r.userid}" title="{$r.firstname} {$r.lastname} ({$r.email})">{$r.username}</a>
{/if}
