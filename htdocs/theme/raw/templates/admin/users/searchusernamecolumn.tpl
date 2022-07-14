{if !$r.active}
  <span class="icon icon-user-times" title="{str tag=inactive section=admin}"></span>
  <span class="visually-hidden">{str tag=inactivefor1 section=admin arg1=$r.username}</span>
{/if}
{if $r.canedituser}
  <a href="{$WWWROOT}admin/users/edit.php?id={$r.id}">{$r.username}</a>
{else}
  {$r.username}
{/if}