{assign var=canedituser value=$USER->get('admin')}
{if !$canedituser && $USER->is_institutional_admin()}
  {foreach from=$r.institutions item=i}
     {if $USER->is_institutional_admin($i)}{assign var=canedituser value=1}{/if}
  {/foreach}
{/if}
{if !$r.active}
    <span class="icon icon-user-times" title="{str tag=inactive section=admin}"></span>
    <span class="sr-only">{str tag=inactivefor1 section=admin arg1=$r.username}</span>
{/if}
{if $canedituser}
{* PCNZ customisation WR 349169 *}
{if $r.registeredstatus == 3}
{* Registered status, inactive *}
<span class="icon icon-user-minus" title="{str tag=registeredstatusinactive section=admin}"></span>
<span class="sr-only">{str tag=registeredstatusinactivefor section=admin arg1=$r.username}</span>
{/if}
{* End of customisation *}
  <a href="{$WWWROOT}admin/users/edit.php?id={$r.id}"> {$r.username}</a>
{else}
  {$r.username}
{/if}
