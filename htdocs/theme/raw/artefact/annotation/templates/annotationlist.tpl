{foreach from=$data item=item}
    <li class="{cycle name=rows values='r0,r1'}{if $item->highlight} list-group-item-warning{/if}{if $item->makepublicform} list-group-item-warning{/if} list-group-item">
        {if $item->author}
            <a href="{$item->author->profileurl}" class="user-icon small-icon">
                <img src="{profile_icon_url user=$item->author maxheight=25 maxwidth=25}" alt="{str tag=profileimagetext arg1=$item->author|display_default_name}">
            </a>
        {else}
        <span class="user-icon small-icon left">
            <img src="{profile_icon_url user=null maxheight=20 maxwidth=20}" alt="{str tag=profileimagetextanonymous}">
        </span>
        {/if}
        <div class="">
                {if $item->makepublicform}
                <div class="makepublicbtn">{$item->makepublicform|safe}</div>
                {/if}

                {if $item->canedit}{/if}



                    <div class="text-right">
                        <div class="btn-action-list">
                            <div class="text-right btn-top-right btn-group btn-group-top">

                                <form class="form-as-button pull-left" name="edit_{$post->id}" action="{$WWWROOT}artefact/annotation/edit.php">
                                    <input type="hidden" name="id" value="{$item->id}">
                                    <input type="hidden" name="viewid" value="{$viewid}">
                                    <button class="btn btn-default btn-sm button">
                                        <span class="fa fa-lg fa-pencil text-default"></span>
                                        <span class="sr-only">{str tag=edit}</span>
                                    </button>
                                </form>

                            </div>
                        </div>
                    </div>






                {if $item->deleteform}{$item->deleteform|safe}{/if}

              {if $item->author}
              <h4 class="title list-group-item-heading mts">
                        <a class="" href="{$item->author->profileurl}">
                            {$item->author|display_name}
                            <span class="metadata text-small">
                                - {$item->date} {if $item->updated}[{str tag=Updated}: {$item->updated}]{/if}
                            </span>
                        </a>
                    </h4>
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
    </li>
{/foreach}
