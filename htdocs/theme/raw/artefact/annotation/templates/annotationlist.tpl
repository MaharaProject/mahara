{foreach from=$data item=item}
    <div class="{cycle name=rows values='r0,r1'}{if $item->highlight} highlight{/if}{if $item->makepublicform} private{/if}">
        <div class="commentleft">
            {if $item->author}
                <a href="{$item->author->profileurl}">
                    <img src="{profile_icon_url user=$item->author maxheight=40 maxwidth=40}" valign="middle" alt="{str tag=profileimagetext arg1=$item->author|display_default_name}">
                </a>
            {else}
                <img src="{profile_icon_url user=null maxheight=20 maxwidth=20}" valign="middle" alt="{str tag=profileimagetextanonymous}">
            {/if}
        </div>
        <div class="commentrightwrap">
              <div class="fr">
                  {if $item->makepublicform}<div class="makepublicbtn">{$item->makepublicform|safe}</div>{/if}
                  {if $item->canedit}
                      <form name="edit_{$post->id}" action="{$WWWROOT}artefact/annotation/edit.php">
                          <input type="hidden" name="id" value="{$item->id}">
                          <input type="hidden" name="viewid" value="{$viewid}">
                          <input type="image" src="{theme_image_url filename="btn_edit"}" title="{str tag=edit}">
                      </form>
                  {/if}
                  {if $item->deleteform}{$item->deleteform|safe}{/if}
              </div>
              {if $item->author}
                  <div class="author">
                      <a href="{$item->author->profileurl}" class="username">{$item->author|display_name}</a>
                      <span class="postedon"> - {$item->date} {if $item->updated}[{str tag=Updated}: {$item->updated}]{/if}</span>
                  </div>
              {else}
                  <div class="author">{$item->authorname}<span class="postedon"> - {$item->date}</span></div>
              {/if}
              {if $item->deletedmessage}
                  <div class="deleteddetails">{$item->deletedmessage}</div>
              {else}
                  <div class="detail">{$item->description|safe|clean_html}</div>
                  {if $item->attachmessage}<div class="attachmessage">{$item->attachmessage}</div>{/if}
                  {if $item->pubmessage}<div class="privatemessage">{$item->pubmessage}</div>{/if}
                  {if $item->makepublicrequested}
                      <div class="requestmessage">{str tag=youhaverequestedpublic section=artefact.annotation}</div>
                  {/if}
              {/if}
        </div>
        <div class="cb"></div>
    </div>
{/foreach}
