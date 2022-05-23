<!-- The "feedbacktable" class is used as an identifier by Javascript -->
<div class="list-group list-group-lite list-group-top-border">
{foreach from=$data item=item}
    <div id="comment{$item->id}" class="comment-item list-group-item {if $item->pubmessage}list-group-item-private{elseif $item->deletedmessage}deleted {/if} {cycle name=rows values='r0,r1'} {if $item->indent} indent-{$item->indent}{/if}">
        <div class="flex-row">
            <div class="usericon-heading flex-title flex-row">
                <div class="float-start">
                    <span class="user-icon user-icon-30" role="presentation" aria-hidden="true">
                    {if $item->author && !$item->author->deleted}
                        <a href="{$item->author->profileurl}"><img src="{profile_icon_url user=$item->author maxheight=30 maxwidth=30}" valign="middle" alt="{str tag=profileimagetext arg1=$item->author|display_default_name}"/></a>
                    {else}
                        <img src="{profile_icon_url user=null maxheight=30 maxwidth=30}" valign="middle" alt="{str tag=profileimagetextanonymous}"/>
                    {/if}
                    </span>
                </div>
                <div class="flex-title">
                    <h3 class="list-group-item-heading text-inline">
                        {if $item->author && !$item->author->deleted}
                        <a href="{$item->author->profileurl}">
                            <span>{$item->author|display_name}</span>
                        </a>
                        {elseif $item->author && $item->author->deleted}
                        <span>{$item->author|full_name}</span>
                        {else}
                        <span>{$item->authorname}</span>
                        {/if}

                        <br />

                        <span class="postedon text-small">
                        {$item->date}
                        {if $item->updated}
                            <p class="metadata">[{str tag=Updated}: {$item->updated}]</p>
                        {/if}
                        </span>
                        {if $item->ratingdata}

                        <span class="star-comment-rating">
                        {for i $item->ratingdata->min_rating $item->ratingdata->max_rating}
                            <div class="star-rating star-rating-readonly">
                                <a class="icon-{$star} {if $i <= $item->ratingdata->value}icon{else}icon-regular{/if}" {if $colour}style="color: {$colour}"{/if}>&nbsp;</a>
                            </div>
                        {/for}
                        </span>
                        {/if}
                    </h3>
                </div>
                <div class="flex-controls">
                    <!-- The "comment-item-buttons" class is used as an identifier by Javascript -->
                    <div class="btn-group btn-group-top comment-item-buttons">
                    {if !$onview}
                        {if $item->canedit}
                        <button data-url="{$WWWROOT}artefact/comment/edit.php?id={$item->id}&amp;view={$viewid}" type="button" class="btn btn-secondary btn-sm btn-group-item form-as-button float-start">
                            <span class="icon icon-pencil-alt" role="presentation" aria-hidden="true"></span>
                            <span class="visually-hidden">{str tag=edit}</span>
                        </button>
                        {/if}
                    {/if}
                    {if $item->deleteform}
                        {$item->deleteform|safe}
                    {/if}
                    {if $item->canreply}
                    <button class="btn btn-secondary btn-sm float-start commentreplyto btn-group-item js-reply" id="commentreplyto{$item->id}" title="{str tag=reply section=artefact.comment}" data-replyto="{$item->id}" data-canprivatereply="{$item->canprivatereply}" data-canpublicreply="{$item->canpublicreply}" {if $blockid}data-blockid="{$blockid}"{/if}>
                        <span class="icon icon-reply" role="presentation" aria-hidden="true"></span>
                        <span class="visually-hidden">{str tag=reply section=artefact.comment}</span>
                    </button>
                    {/if}
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-8 comment-text">
                {if $item->deletedmessage}
                    <div class="push-left-for-usericon"><span class="metadata">{$item->deletedmessage}</span></div>
                {else}
                    {if $item->author}
                        <div class="push-left-for-usericon">{$item->description|clean_html|safe}</div>
                    {else}
                        <div class="push-left-for-usericon">{$item->description|safe}</div>
                    {/if}
                {/if}

                <div class="comment-privacy metadata push-left-for-usericon">
                    {if $item->pubmessage}
                        <em class="privatemessage"> {$item->pubmessage} </em>
                    {/if}

                    {if $item->makepublicform || ($item->makepublicrequested && !$item->deletedmessage)}
                        {* comment author wants their private comment public - for page owner*}
                        {if !$item->requested_by_usr && $item->makepublicrequested === 'author' && $item->makepublicrequested}
                            <em>{str tag=moderatecomment section=artefact.comment}</em>
                        {/if}

                        {* view owner wants sb else's comment public - for commenter*}
                        {if !$item->requested_by_usr && $item->makepublicrequested === 'owner' && $item->makepublicrequested}
                            <em>{str tag=privatetopubliccomment section=artefact.comment}</em>
                        {/if}

                        {if $item->makepublicform}
                            {$item->makepublicform|safe}
                        {/if}

                        {* the usr has made a request to make a comment public *}
                        {if $item->requested_by_usr && $item->makepublicrequested}
                            <em>{str tag=youhaverequestedpublic section=artefact.comment}</em>
                        {/if}

                    {/if}
                </div>
            </div>

            {if !$item->deletedmessage && $item->attachments}
            <div class="col-md-4 comment-attachment push-left-for-usericon">
                <div class="card has-attachment collapsible">
                    <div class="card-header">
                        <a class="collapsible collapsed" aria-expanded="false" href="#attachments_{$item->id}" data-bs-toggle="collapse">
                            <span class="icon left icon-paperclip icon-sm" role="presentation" aria-hidden="true"></span>
                            <span class="text-small">{str tag=Attachments section=artefact.comment} ({$item->filescount})</span>
                            <span class="icon icon-chevron-down float-end collapse-indicator" role="presentation" aria-hidden="true"></span>
                        </a>
                    </div>
                    <div id="attachments_{$item->id}" class="collapse" aria-expanded="false">
                        <ul class="list-unstyled list-group">
                        {strip}
                            {foreach $item->attachments item=a name=attachments}
                            <li class="flex-row list-group-item">
                                <span class="flex-title title attachment-title">
                                    <a href="{$WWWROOT}artefact/file/download.php?file={$a->attachid}&comment={$item->id}&view={$viewid}"  title="{$a->attachtitle}">
                                        <span class="text-small">{$a->attachtitle}</span>
                                    </a>
                                </span>
                                <a href="{$WWWROOT}artefact/file/download.php?file={$a->attachid}&comment={$item->id}&view={$viewid}" class="download-link">
                                    <span class="icon icon-download icon-lg float-end text-watermark icon-action" role="presentation" aria-hidden="true" data-bs-toggle="tooltip" title="{str tag=downloadfilesize section=artefact.file arg1=$a->attachtitle arg2=$a->attachsize}"></span>
                                    <span class="visually-hidden">{str tag=downloadfilesize section=artefact.file arg1=$a->attachtitle arg2=$a->attachsize}</span>
                                </a>
                            </li>
                            {/foreach}
                        {/strip}
                        </ul>
                    </div>
                </div>
            {if $item->attachmessage}
                <em class="attachmessage metadata">{$item->attachmessage}</em>
            {/if}
            </div>
            {/if}
        </div>
    </div>
{/foreach}
</div>
