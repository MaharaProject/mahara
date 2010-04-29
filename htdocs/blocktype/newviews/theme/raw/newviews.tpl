{if $views}
  {foreach from=$views item=view}
    <div><strong><a href="{$WWWROOT}view/view.php?id={$view.id}">{$view.title|escape}</a></strong></div>
    <div>{$view.shortdescription}</div>
    {if $view.sharedby}
    <div>
      {if $view.group && $loggedin}
        <a href="{$WWWROOT}group/view.php?id={$view.group|escape}">{$view.sharedby|escape}</a>
      {elseif $view.owner && $loggedin}
        <a href="{$WWWROOT}user/view.php?id={$view.owner|escape}">{$view.sharedby|escape}</a>
      {else}
        {$view.sharedby|escape}
      {/if}
      <span class="postedon">
        {if $view.mtime == $view.ctime}{str tag=Created}{else}{str tag=Updated}{/if}
        {$view.mtime|strtotime|format_date:'strftimedate'}
      </span>
    </div>
    {/if}
  {/foreach}
{/if}
