  {foreach from=$data item=item}
    <tr class="{cycle name=rows values='r0,r1'}">
      <td>
        {if $item->deleted}
          <span class="details">[{str tag=commentremoved section=artefact.comment}]</span>
        {else}
          {$item->description|clean_html}
          {if $item->attachmessage}<div>{$item->attachmessage}</div>{/if}
        {/if}
        <div class="details">
        {if $item->deleteform}<div class="fr">{$item->deleteform}</div>{/if}
        {if $item->author}
          <div class="icon"><a href="{$WWWROOT}user/view.php?id={$item->author->id|escape}">
            <img src="{profile_icon_url user=$item->author maxheight=20 maxwidth=20}" valign="middle" alt="{$item->author|display_name}">
          </a></div>
          <a href="{$WWWROOT}user/view.php?id={$item->author->id|escape}">{$item->author|display_name}</a>
        {else}
          {$item->authorname|escape}
        {/if}
        {if $item->deleted}
          | {$item->deletedmessage|escape}
        {else}
          | {$item->date|escape}
          {if $item->pubmessage}
             | {$item->pubmessage|escape}{if $item->makeprivateform}{$item->makeprivateform}{/if}
          {/if}
          {strip}
          {foreach $item->attachments item=a name=attachments}
            {if $.foreach.attachments.first} | {str tag=Attachments section=artefact.comment}:{else},{/if} <a href="{$WWWROOT}artefact/file/download.php?file={$a->attachid}">{$a->attachtitle|escape}</a> ({$a->attachsize|escape})
          {/foreach}
          {/strip}
          {if $item->canedit} | <a href="{$WWWROOT}artefact/comment/edit.php?id={$item->id}&view={$viewid}">{str tag=edit}</a>{/if}
        {/if}
        </div>
      </td>
    </tr>
  {/foreach}
