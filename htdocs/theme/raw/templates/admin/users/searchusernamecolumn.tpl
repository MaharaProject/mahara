{if !$r.active}
  <span class="icon icon-user-times" title="{str tag=inactive section=admin}"></span>
  <span class="sr-only">{str tag=inactivefor1 section=admin arg1=$r.username}</span>
{/if}
{*PCNZ customization WR 34916*}
{if $r.canedituser}
{*PCNZ customisation WR 356027*}
{if $r.registeredstatus == 3}
{* Registered status, inactive *}
<span class="icon icon-user-minus" title="{str tag=registeredstatusinactive section=admin}"></span>
<span class="sr-only">{str tag=registeredstatusinactivefor section=admin arg1=$r.username}</span>
{/if}
{*PCNZ customisation WR 356091*}
{if $r.registeredstatus == 4}
<span class="icon icon-user-minus" title="{str tag=registeredsuspended section=admin}"></span>
<span class="sr-only">{str tag=registeredstatussuspendedfor section=admin arg1=$r.username}</span>
{/if}
{* End of customisation *}
  <a href="{$WWWROOT}admin/users/edit.php?id={$r.id}">{$r.username}</a>
{else}
  {$r.username}
{/if}