{foreach from=$views item=view}
    <tr>
      <td class="sharedpages">
        <h3 class="title"><a href="{$view.fullurl}">{$view.title|str_shorten_text:65:true}</a>{if $view.collid} ({str tag=nviews section=view arg1=$view.numpages}){/if}</h3>
        {if $view.sharedby}
          <div class="groupdate">
          {if $view.group}
            <a href="{$view.groupdata->homeurl}">{$view.sharedby}</a>
          {elseif $view.owner}
            {if $view.anonymous}
                {str tag=anonymoususer section=mahara}
            {else}
                <a href="{profile_url($view.user)}">{$view.sharedby}</a>
            {/if}
          {else}
            {$view.sharedby}
          {/if}
            <span class="postedon text-midtone"> - {if $view.mtime == $view.ctime}
                    {str tag=Created}
                {else}
                    {str tag=Updated}
                {/if}
                {$view.mtime|strtotime|format_date:'strftimedate'}</span>
          </div>
        {/if}
        <div class="detail">{$view.description|str_shorten_html:70:true|strip_tags|safe}</div>
        {if $view.tags}<div class="tags"><strong>{str tag=tags}:</strong> {list_tags owner=$view.owner tags=$view.tags}</div>{/if}
      </td>
      <td class="text-center">{$view.commentcount}</td>
      <td class="lastcomment">
        {if $view.commenttext}
            <div class="comment">
                <a href="{$WWWROOT}view/view.php?id={$view.lastcommentviewid}&showcomment={$view.commentid}" title="{str tag=viewcomment section=artefact.comment}">{$view.commenttext|str_shorten_html:40:true|strip_tags|safe}</a>
            </div>
            <span class="postedon text-midtone text-block">{$view.lastcommenttime|strtotime|format_date:'strftimerecentyear'}</span>
          {if $view.commentauthor}
            <span class="poster text-small">
                <a href="{profile_url($view.commentauthor)}">
                    <span class="text-inline user-icon user-icon-20 user-icon-inline">
                        <img src="{profile_icon_url user=$view.commentauthor maxwidth=20 maxheight=20}" alt="{str tag=profileimagetext arg1=$view.commentauthor|display_default_name}" class="profile-icon-container">
                    </span>
                    {$view.commentauthor|display_name}
                </a>
            </span>
          {else}
            <span class="poster">{$view.commentauthorname} - </span>
          {/if}
        {/if}
      </td>
    </tr>
{/foreach}
