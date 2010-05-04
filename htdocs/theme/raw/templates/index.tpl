{auto_escape off}
{include file="header.tpl"}
{$page_content|clean_html}
{if $views}
  <h5>{str tag=recentupdates}</h5>
  <table>
  {foreach from=$views item=view}
  <tr>
    {if $view.sharedby}
    <td>
      {if $view.group && $USER->is_logged_in()}
        <a href="{$WWWROOT}group/view.php?id={$view.group}">{$view.sharedby}</a>
      {elseif $view.owner && $USER->is_logged_in()}
        <a href="{$WWWROOT}user/view.php?id={$view.owner}">{$view.sharedby}</a>
      {else}
        {$view.sharedby}
      {/if}
      <div>{$view.mtime|strtotime|format_date:'strftimedate'}</div>
    </td>
    {/if}
    <td>
      <div><strong><a href="{$WWWROOT}view/view.php?id={$view.id}">{$view.title|escape}</a></strong></div>
      <div>{$view.shortdescription}</div>
    </td>
  </tr>
  {/foreach}
  </table>
{/if}
{include file="footer.tpl"}
{/auto_escape}
