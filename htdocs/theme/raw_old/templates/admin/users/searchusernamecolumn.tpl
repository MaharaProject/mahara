{assign var=canedituser value=$USER->get('admin')}
{if !$canedituser && $USER->is_institutional_admin()}
  {foreach from=$r.institutions item=i}
     {if $USER->is_institutional_admin($i)}{assign var=canedituser value=1}{/if}
  {/foreach}
{/if}
{if !$r.active}
    <span class="icon icon-user-times" title="{str tag=inactive section=admin}"></span>
    <span class="sr-only">{str tag=inactivefor section=admin arg1=$r.username}</span>
{/if}
{if $canedituser}
  <a href="{$WWWROOT}admin/users/edit.php?id={$r.id}">{$r.username}</a>
{else}
  {$r.username}
{/if}
