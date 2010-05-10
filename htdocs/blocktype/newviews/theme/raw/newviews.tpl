{if $views}
  <ul class="viewlist">
  {foreach from=$views item=view}
    <li>
      <div><strong><a href="{$WWWROOT}view/view.php?id={$view.id}">{$view.title}</a></strong></div>
      <div>{$view.shortdescription|safe|clean_html}</div>
      {if $view.sharedby}
      <div>
        {if $view.group && $loggedin}
          <a href="{$WWWROOT}group/view.php?id={$view.group}">{$view.sharedby}</a>
        {elseif $view.owner && $loggedin}
          <a href="{$WWWROOT}user/view.php?id={$view.owner}">{$view.sharedby}</a>
        {else}
          {$view.sharedby}
        {/if}
        <span class="postedon">
          {if $view.mtime == $view.ctime}{str tag=Created}{else}{str tag=Updated}{/if}
          {$view.mtime|strtotime|format_date:'strftimedate'}
        </span>
      </div>
      {/if}
    </li>
  {/foreach}
  </ul>
{else}
  {str tag=noviews section=view}
{/if}
