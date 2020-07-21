<!-- The "feedbacktable" class is used as an identifier by Javascript -->
<div class="list-group list-group-lite list-group-top-border">
{foreach from=$data item=item}
    <div id="comment{$item->id}" class="comment-item list-group-item {if $item->pubmessage}list-group-item-private{elseif $item->deletedmessage}deleted {/if} {cycle name=rows values='r0,r1'} {if $item->indent} indent-{$item->indent}{/if} {if !$item->deletedmessage && $item->attachments}has-attachment{/if}">
        <div class="usericon-heading">
            <span class="user-icon user-icon-30 float-left" role="presentation" aria-hidden="true">
                {if $item->author && !$item->author->deleted}
                    <a href="{$item->author->profileurl}"><img src="{profile_icon_url user=$item->author maxheight=30 maxwidth=30}" valign="middle" alt="{str tag=profileimagetext arg1=$item->author|display_default_name}"/></a>
                {else}
                    <img src="{profile_icon_url user=null maxheight=30 maxwidth=30}" valign="middle" alt="{str tag=profileimagetextanonymous}"/>
                {/if}
            </span>
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
                            <a class="icon icon-{$star} {if $i <= $item->ratingdata->value}icon-solid{else}icon-regular{/if}" {if $colour}style="color: {$colour}"{/if}>&nbsp;</a>
                        </div>
                    {/for}
                </span>
                {/if}
            </h3>
            <!-- The "comment-item-buttons" class is used as an identifier by Javascript -->
            <div class="btn-group btn-group-top comment-item-buttons">
                {if !$onview}
                    {if $item->canedit}
                    <a href="{$WWWROOT}artefact/comment/edit.php?id={$item->id}&amp;view={$viewid}" class="btn btn-secondary btn-group-item form-as-button float-left">
                        <span class="icon icon-pencil-alt" role="presentation" aria-hidden="true"></span>
                        <span class="sr-only">{str tag=edit}</span>
                    </a>
                    {/if}
                {/if}
                {if $item->deleteform}
                    {$item->deleteform|safe}
                {/if}
                {if $item->canreply}
                <button class="btn btn-secondary float-left commentreplyto btn-group-item js-reply" id="commentreplyto{$item->id}" title="{str tag=reply section=artefact.comment}" data-replyto="{$item->id}" data-canprivatereply="{$item->canprivatereply}" data-canpublicreply="{$item->canpublicreply}" {if $blockid}data-blockid="{$blockid}"{/if}>
                    <span class="icon icon-reply" role="presentation" aria-hidden="true"></span>
                    <span class="sr-only">{str tag=reply section=artefact.comment}</span>
                </button>
                {/if}
            </div>
        </div>

        <div class="row">
            <div class="col-md-8 comment-text">
                <div class="comment-content">
                    {if $item->deletedmessage}
                        <span class="metadata">
                            {$item->deletedmessage}
                        </span>
                    {else}
                        {if $item->author}
                            {$item->description|clean_html|safe}
                        {else}
                            {$item->description|safe}
                        {/if}
                    {/if}
                </div>

                {if $item->makepublicform || ($item->makepublicrequested && !$item->deletedmessage)}
                <div class="metadata">
                    {if $item->pubmessage}
                    <em class="privatemessage"> {$item->pubmessage} </em> -
                    {/if}

                    {if $item->makepublicform}
                        {$item->makepublicform|safe}
                    {/if}

                    {if $item->makepublicrequested && !$item->deletedmessage}
                        <span class="icon icon-lock text-default left" role="presentation" aria-hidden="true"></span>
                        <span>{str tag=youhaverequestedpublic section=artefact.comment}</span>
                    {/if}
                </div>
                {/if}
            </div>

            {if !$item->deletedmessage && $item->attachments}
            <div class="col-md-4 comment-attachment">
                <div class="card has-attachment collapsible">
                    <div class="card-header">
                        <a class="collapsible collapsed" aria-expanded="false" href="#attachments_{$item->id}" data-toggle="collapse">
                            <span class="icon left icon-paperclip icon-sm" role="presentation" aria-hidden="true"></span>
                            <span class="text-small">{str tag=Attachments section=artefact.comment} ({$item->filescount})</span>
                            <span class="icon icon-chevron-down float-right collapse-indicator" role="presentation" aria-hidden="true"></span>
                        </a>
                    </div>
                    <div id="attachments_{$item->id}" class="collapse" aria-expanded="false">
                        <ul class="list-unstyled list-group">
                        {strip}
                            {foreach $item->attachments item=a name=attachments}
                            <li class="list-group-item">
                                <span class="title">
                                    <a href="{$WWWROOT}artefact/file/download.php?file={$a->attachid}&comment={$item->id}&view={$viewid}">
                                        <span class="text-small">{$a->attachtitle}</span>
                                    </a>
                                    <span class="text-midtone text-small"> [{$a->attachsize}]</span>
                                </span>
                                <a href="{$WWWROOT}artefact/file/download.php?file={$a->attachid}&comment={$item->id}&view={$viewid}" class="download-link">
                                    <span class="icon icon-download icon-lg float-right text-watermark icon-action" role="presentation" aria-hidden="true"></span>
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
