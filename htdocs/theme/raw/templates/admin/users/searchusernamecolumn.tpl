{assign var=canedituser value=$USER->get('admin')}
{if !$canedituser && $USER->is_institutional_admin()}
  {foreach from=$r.institutions item=i}
     {if $USER->is_institutional_admin($i)}{assign var=canedituser value=1}{/if}
  {/foreach}
{/if}
{if $canedituser}
  <a href="{$WWWROOT}admin/users/edit.php?id={$r.id}">{$r.username}</a>
{else}
  {$r.username}
{/if}
