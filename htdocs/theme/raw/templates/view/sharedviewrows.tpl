{foreach from=$views item=view}
    <tr>
      <td class="sharedpages">
        <h2 class="title"><a href="{$view.fullurl}">{$view.title|str_shorten_text:65:true}</a>{if $view.collid} ({str tag=nviews1 section=view arg1=$view.numpages}){/if}</h2>
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
        <div class="detail text-small">{$view.description|str_shorten_html:70:true|strip_tags|safe}</div>
        {if $view.tags}<div class="tags text-small">{str tag=tags}: {list_tags owner=$view.owner tags=$view.tags}</div>{/if}
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
      {if $completionvisible}
        <td class='completion cell-center'>{$view.progresspercentage|safe}</td>
        <td class='verification cell-center'>
          {if is_int($view.verification)}
            {if $view.verification}
            <div class="icon text-success icon-check-square" role="presentation" title='{str tag=verificationdone arg1=$view.title arg2=$view.sharedby section=collection}'><span class="visually-hidden">{str tag=sharedviewverifiedchecked arg1=$view.title arg2=$view.sharedby section=collection}</span></div></a>
            {else}
            <a href="{$WWWROOT}collection/progresscompletion.php?id={$view.collid}"><div class="icon icon-regular icon-square" role="presentation" title='{str tag=verificationtobedone section=collection arg1=$view.title arg2=$view.sharedby}'><span class="visually-hidden">{str tag=sharedviewverifiedunchecked arg1=$view.title arg2=$view.sharedby section=collection}</span></div></a>
            {/if}
          {else}
            {$view.verification|safe}
          {/if}
        </td>
      {/if}
      {if $canremoveownaccess}
        <td class='revokemyaccess cell-center'>
          {if $view.accessrevokable}
            <button class="deletebutton btn btn-inverse btn-sm" data-bs-toggle="modal" data-bs-target="#revokemyaccess-form" data-viewid={$view.viewid} data-title="{$view.title}" title='{str tag=removeaccess arg1=$view.title arg2=$view.sharedby section=collection}'>
              <span class="icon icon-trash-alt text-danger" aria-label='{str tag=removemyaccessiconaria arg1=$view.title arg2=$view.sharedby section=collection}'></span>
            </button>
          {/if}
        </td>
      {/if}
    </tr>
{/foreach}
