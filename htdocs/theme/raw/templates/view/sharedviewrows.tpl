{foreach from=$views item=view}
    <tr class="{cycle values='r0,r1'}">
      <td class="sharedpages">
        <h3><a href="{$WWWROOT}view/view.php?id={$view.id}">{$view.title|str_shorten_text:65:true}</a></h3>
        {if $view.sharedby}
        <span class="owner">
          {if $view.group}
            <a href="{$WWWROOT}group/view.php?id={$view.group}">{$view.sharedby}</a>
          {elseif $view.owner}
            <a href="{$WWWROOT}user/view.php?id={$view.owner}">{$view.sharedby}</a>
          {else}
            {$view.sharedby}
          {/if}
        </span>
        <span class="postedon nowrap"> - {$view.mtime|strtotime|format_date:'strftimerecent'}</span>
        {/if}
        <div class="s">{$view.description|str_shorten_html:70:true|strip_tags|safe}</div>
        {if $view.tags}<div class="tags"><label>{str tag=tags}:</label> {list_tags owner=$view.owner tags=$view.tags}</div>{/if}
      </td>
      <td class="center">{$view.commentcount}</td>
      <td class="lastcomment s">
        {if $view.commenttext}
            <a href="{$WWWROOT}view/view.php?id={$view.id}&showcomment={$view.commentid}" class="fr btn" title="{str tag=viewcomment section=artefact.comment}">{str tag=viewcomment section=artefact.comment}</a>
            <div>{$view.commenttext|str_shorten_html:40:true|strip_tags|safe}</div>
          {if $view.commentauthor}
            <a href="{$WWWROOT}user/view.php?id={$view.commentauthor}" class="poster">{$view.commentauthor|display_name}</a>
          {else}
            {$view.commentauthorname}
          {/if}
            <span class="postedon nowrap">{$view.lastcommenttime|strtotime|format_date:'strftimerecent'}</span>
        {/if}
      </td>
    </tr>
{/foreach}
