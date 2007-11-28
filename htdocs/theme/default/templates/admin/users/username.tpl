{if $USER->get('admin') || !empty($r.institutions)}
<a href="{$WWWROOT}admin/users/edit.php?id={$r.id}">{$r.username}</a>
{else}
{$r.username}
{/if}