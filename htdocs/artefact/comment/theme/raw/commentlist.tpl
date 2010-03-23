  {foreach from=$data item=item}
    <tr class="{cycle name=rows values='r0,r1'}">
      <td>
        {$item->description|clean_html}
        {if $item->attachmessage}<div>{$item->attachmessage}</div>{/if}
        <div class="details">
        {if $item->author}
          <div class="icon"><a href="{$WWWROOT}user/view.php?id={$item->author|escape}">
            <img src="{$WWWROOT}thumb.php?type=profileicon&id={$item->author|escape}&maxsize=20" valign="middle" alt="{$item->author|display_name}">
          </a></div>
          <a href="{$WWWROOT}user/view.php?id={$item->author|escape}">{$item->author|display_name}</a>
        {else}
          {$item->authorname|escape}
        {/if}
        | {$item->date|escape}
        {if $item->pubmessage}
           | {$item->pubmessage|escape}{if $item->makeprivateform}{$item->makeprivateform}{/if}
        {/if}
        {strip}
        {foreach $item->attachments item=a name=attachments}
          {if $.foreach.attachments.first} | {str tag=Attachments section=artefact.comment}:{else},{/if} <a href="{$WWWROOT}artefact/file/download.php?file={$a->attachid}">{$a->attachtitle|escape}</a> ({$a->attachsize|escape})
        {/foreach}
        {/strip}
        </div>
      </td>
    </tr>
  {/foreach}
