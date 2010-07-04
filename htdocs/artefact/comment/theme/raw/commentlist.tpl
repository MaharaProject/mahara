{foreach from=$data item=item}
  <tr class="{cycle name=rows values='r0,r1'}{if $item->highlight} highlight{/if}">
    <td>
      {if $item->deletedmessage}
        <span class="details">{str tag=commentremoved section=artefact.comment}</span>
      {else}
        {$item->description|safe|clean_html}
        {if $item->attachmessage}<div>{$item->attachmessage}</div>{/if}
      {/if}
      <div class="details">
        <div class="fr">
          {if $item->makepublicform}{$item->makepublicform|safe}{/if}
          {if $item->deleteform}{$item->deleteform|safe}{/if}
        </div>
      {if $item->author}
        <div class="icon"><a href="{$WWWROOT}user/view.php?id={$item->author->id}">
          <img src="{profile_icon_url user=$item->author maxheight=20 maxwidth=20}" valign="middle" alt="{$item->author|display_name|escape}">
        </a></div>
        <a href="{$WWWROOT}user/view.php?id={$item->author->id}">{$item->author|display_name|escape}</a>
      {else}
        {$item->authorname}
      {/if}
      {if $item->deletedmessage}
        | {$item->deletedmessage}
      {else}
        | {$item->date}
        {if $item->pubmessage} | {$item->pubmessage}{/if}
        {if $item->makepublicrequested} | {str tag=youhaverequestedpublic section=artefact.comment}{/if}
        {strip}
        {foreach $item->attachments item=a name=attachments}
          {if $.foreach.attachments.first} | {str tag=Attachments section=artefact.comment}:{else},{/if} <a href="{$WWWROOT}artefact/file/download.php?file={$a->attachid}">{$a->attachtitle}</a> ({$a->attachsize})
        {/foreach}
        {/strip}
        {if $item->canedit} | <a href="{$WWWROOT}artefact/comment/edit.php?id={$item->id}&view={$viewid}">{str tag=edit}</a>{/if}
      {/if}
      </div>
    </td>
  </tr>
{/foreach}
