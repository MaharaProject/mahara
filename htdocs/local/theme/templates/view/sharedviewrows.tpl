{foreach from=$views item=view}
    <tr>
      <td class="sharedpages">
        <h2 class="title"><a href="{$view.fullurl}">{$view.title|str_shorten_text:65:true}</a>{if $view.collid} ({str tag=nviews section=view arg1=$view.numpages}){/if}</h2>
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
        {if $view.tags}<div class="tags text-small"><strong>{str tag=tags}:</strong> {list_tags owner=$view.owner tags=$view.tags}</div>{/if}
      </td>
      {if $completionvisible}
        <td class='completion cell-center'>{$view.progresspercentage|safe}</td>
        <td class='verification cell-center'>
          {if is_int($view.verification)}
            {if $view.verification}
            <div class="icon text-success icon-check-square" role="presentation" title='{str tag=verificationdone arg1=$view.title arg2=$view.sharedby section=collection}'><span class="sr-only">{str tag=sharedviewverifiedchecked arg1=$view.title arg2=$view.sharedby section=collection}</span></div></a>
            {else}
            <a href="{$WWWROOT}collection/progresscompletion.php?id={$view.collid}"><div class="icon icon-regular icon-square" role="presentation" title='{str tag=verificationtobedone section=collection arg1=$view.title arg2=$view.sharedby}'><span class="sr-only">{str tag=sharedviewverifiedunchecked arg1=$view.title arg2=$view.sharedby section=collection}</span></div></a>
            {/if}
          {else}
            {$view.verification|safe}
          {/if}
        </td>
      {/if}
      {if $canremoveownaccess}
        <td class='revokemyaccess cell-center'>
          {if $view.accessrevokable}
            <button class="deletebutton btn btn-inverse btn-sm" data-toggle="modal" data-target="#revokemyaccess-form" data-viewid={$view.viewid} data-title="{$view.title}" title='{str tag=removeaccess arg1=$view.title arg2=$view.sharedby section=collection}'>
              <span class="icon icon-trash-alt text-danger" aria-label='{str tag=removemyaccessiconaria arg1=$view.title arg2=$view.sharedby section=collection}'></span>
            </button>
          {/if}
        </td>
      {/if}
    </tr>
{/foreach}
