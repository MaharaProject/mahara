{foreach from=$views item=view}
    <tr class="{cycle values='r0,r1'}">
      <td class="sharedpages">
        <h4><a href="{$view.fullurl}">{$view.title|str_shorten_text:65:true}</a></h4>
        {if $view.sharedby}
        <span class="owner">
          {if $view.group}
            <a href="{group_homepage_url($view.groupdata)}">{$view.sharedby}</a>
          {elseif $view.owner}
            <a href="{profile_url($view.user)}">{$view.sharedby}</a>
          {else}
            {$view.sharedby}
          {/if}
        </span>
        <span class="postedon nowrap"> - {$view.mtime|strtotime|format_date:'strftimerecentyear'}</span>
        {/if}
        <div class="sharepagedescription">{$view.description|str_shorten_html:70:true|strip_tags|safe}</div>
        {if $view.tags}<div class="tags"><label>{str tag=tags}:</label> {list_tags owner=$view.owner tags=$view.tags}</div>{/if}
      </td>
      <td class="center">{$view.commentcount}</td>
      <td class="lastcomment">
        {if $view.commenttext}
            <div class="comment">"{$view.commenttext|str_shorten_html:40:true|strip_tags|safe}"
                <a href="{$WWWROOT}view/view.php?id={$view.id}&showcomment={$view.commentid}" title="{str tag=viewcomment section=artefact.comment}">{str tag=viewcomment section=artefact.comment}</a>
            </div>
          {if $view.commentauthor}
            <span class="poster"><a href="{profile_url($view.commentauthor)}">{$view.commentauthor|display_name}</a> - </span>
          {else}
            <span class="poster">{$view.commentauthorname} - </span>
          {/if}
            <span class="postedon nowrap">{$view.lastcommenttime|strtotime|format_date:'strftimerecentyear'}</span>
        {/if}
      </td>
    </tr>
{/foreach}
