        {$comment->message}
        {if $comment->attachmessage}<div>{$comment->attachmessage}</div>{/if}
        <div class="details">
        {if $comment->author}
          <div class="icon"><a href="{$WWWROOT}user/view.php?id={$comment->author|escape}">
            <img src="{$WWWROOT}thumb.php?type=profileicon&id={$comment->author|escape}&maxsize=20" valign="middle" alt="{$comment->author|display_name}">
          </a></div>
          <a href="{$WWWROOT}user/view.php?id={$comment->author|escape}">{$comment->author|display_name}</a>
        {else}
          {$comment->authorname|escape}
        {/if}
        | {$comment->date|escape}
        {if $comment->pubmessage}
           | {$comment->pubmessage|escape}{if $comment->makeprivateform}{$comment->makeprivateform}{/if}
        {/if}
        {if $comment->attachid}
           | {str tag=attachment section=view}: <a href="{$WWWROOT}artefact/file/download.php?file={$comment->attachid}">{$comment->attachtitle|escape}</a> ({$comment->attachsize|escape})
        {/if}
        </div>
