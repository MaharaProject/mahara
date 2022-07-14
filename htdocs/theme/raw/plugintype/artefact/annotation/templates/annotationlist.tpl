{foreach from=$data item=item}
<div class="{if $item->highlight} list-group-item-warning{/if}{if $item->makepublicform} list-group-item-private{/if} list-group-item comment-item">
    <div class="flex-row">
        <div class="usericon-heading flex-title flex-row">
            <div class="float-start">
                <span class="user-icon user-icon-30" role="presentation" aria-hidden="true">
                {if $item->author}
                    <img src="{profile_icon_url user=$item->author maxheight=30 maxwidth=30}" alt="{str tag=profileimagetext arg1=$item->author|display_default_name}">
                {else}
                    <img src="{profile_icon_url user=null maxheight=30 maxwidth=30}" alt="{str tag=profileimagetextanonymous}">
                {/if}
                </span>
            </div>
            <div class="flex-title">
                <h2 class="list-group-item-heading text-inline">
                    {if $item->author}
                    <a href="{$item->author->profileurl}">
                    {/if}
                        <span>{$item->author|display_name}</span>
                    {if $item->author}
                    </a>
                    {/if}

                    <br />

                    <span class="postedon text-small detail">
                    {$item->date}
                    {if $item->updated}
                        <p class="metadata">[{str tag=Updated}: {$item->updated}]</p>
                    {/if}
                    </span>
                </h2>
            </div>
            <div class="flex-controls">
                <div class="btn-group btn-group-top comment-item-buttons">
                    {if $item->canedit}
                    <button data-url="{$WWWROOT}artefact/annotation/edit.php?id={$item->id}&amp;viewid={$viewid}" type="button" class="btn btn-secondary btn-sm">
                        <span class="icon icon-pencil-alt text-default" role="presentation" aria-hidden="true"></span>
                        <span class="visually-hidden">{str tag=edit}</span>
                    </button>
                    {/if}
                    {if $item->deleteform}
                        {$item->deleteform|safe}
                    {/if}
                </div>
            </div>
        </div>
    </div>


    <div class="content-text push-left-for-usericon">
    {if $item->deletedmessage}
        <span class="metadata">
            {$item->deletedmessage}
        </span>
    {else}
        {$item->description|clean_html|safe}

        {if $item->attachmessage}
        <div class="attachmessage">
            {$item->attachmessage}
        </div>
        {/if}

        {if $item->makepublicform || $item->makepublicrequested || $item->pubmessage}
        <div class="comment-privacy metadata">
            {if $item->pubmessage}
            <em class="privatemessage">
                {$item->pubmessage}
            </em>
            {/if}

            {if $item->makepublicform}
                {$item->makepublicform|safe}
            {/if}

            {if $item->makepublicrequested}
            <span class="icon icon-lock text-default left" role="presentation" aria-hidden="true"></span>
            {/if}
        </div>
        {/if}
    {/if}
    </div>
</div>
{/foreach}
