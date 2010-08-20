{if $views}
  <table class="viewlist">
  {foreach from=$views item=view}
    <tr>
            <td class="{cycle values='r0,r1'}"><h4><a href="{$WWWROOT}view/view.php?id={$view.id}">{$view.title}</a></h4>
              <div class="details">{$view.shortdescription|safe|clean_html}</div>
              {if $view.sharedby}
              <div>
                {if $view.group && $loggedin}
                  <a href="{$WWWROOT}group/view.php?id={$view.group}" class="s">{$view.sharedby}</a>
                {elseif $view.owner && $loggedin}
                  <a href="{$WWWROOT}user/view.php?id={$view.owner}" class="s">{$view.sharedby}</a>
                {else}
                  {$view.sharedby}
                {/if}
                <span class="postedon">
                  {if $view.mtime == $view.ctime}{str tag=Created}{else}{str tag=Updated}{/if}
                  {$view.mtime|strtotime|format_date:'strftimedate'}
                </span>
              </div>
              {/if}
            </td>
        </tr>
  {/foreach}
  </table>
{else}
  {str tag=noviews section=view}
{/if}
