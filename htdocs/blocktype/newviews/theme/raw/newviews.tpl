{if $views}
  <table class="viewlist">
  {foreach from=$views item=view}
    <tr class="{cycle values='r0,r1'}">
            <td><h4><a href="{$WWWROOT}view/view.php?id={$view.id}">{$view.title}</a></h4>
              <div class="details">{$view.description|str_shorten_html:100:true|strip_tags|safe}</div></td>
            {if $view.sharedby}
            <td class="right">
                {if $view.group && $loggedin}
                  <a href="{$WWWROOT}group/view.php?id={$view.group}" class="s">{$view.sharedby}</a>
                {elseif $view.owner && $loggedin}
                  <a href="{$WWWROOT}user/view.php?id={$view.owner}" class="s">{$view.sharedby}</a>
                {else}
                  {$view.sharedby}
                {/if}
             	<div class="postedon nowrap">
                  {if $view.mtime == $view.ctime}{str tag=Created}{else}{str tag=Updated}{/if}
                  {$view.mtime|strtotime|format_date:'strftimedate'}</div>
            </td>
            {/if}
        </tr>
  {/foreach}
  </table>
{else}
  {str tag=noviews section=view}
{/if}
