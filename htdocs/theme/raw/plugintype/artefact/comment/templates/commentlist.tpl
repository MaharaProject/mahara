<!-- The "feedbacktable" class is used as an identifier by Javascript -->
<div class="list-group list-group-lite">
{foreach from=$data item=item}
    <div id="comment{$item->id}" class="comment-item list-group-item {if $item->pubmessage}list-group-item-warning{elseif $item->deletedmessage}deleted {/if} {cycle name=rows values='r0,r1'} {if $item->indent} indent-{$item->indent}{/if} {if !$item->deletedmessage && $item->attachments}has-attachment{/if}">
        <div class="usericon-heading">
            <span class="user-icon user-icon-40 float-left" role="presentation" aria-hidden="true">
                {if $item->author && !$item->author->deleted}
                    <img src="{profile_icon_url user=$item->author maxheight=40 maxwidth=40}" valign="middle" alt="{str tag=profileimagetext arg1=$item->author|display_default_name}"/>
                {else}
                    <img src="{profile_icon_url user=null maxheight=40 maxwidth=40}" valign="middle" alt="{str tag=profileimagetextanonymous}"/>
                {/if}
            </span>
            <h5 class="float-left list-group-item-heading">
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
                        <div class="star-rating star star-rating-applied star-rating-readonly {$star}-rating{if $i <= $item->ratingdata->value}-on{else}-off{/if}"><a {if $colour}style="color: {$colour}"{/if}>&nbsp;</a></div>
                    {/for}
                </span>
                {/if}
            </h5>
            <!-- The "comment-item-buttons" class is used as an identifier by Javascript -->
            <div class="btn-group btn-group-top comment-item-buttons">
                {if !$onview}
                    {if $item->canedit}
                    <a href="{$WWWROOT}artefact/comment/edit.php?id={$item->id}&amp;view={$viewid}" class="btn btn-secondary btn-group-item form-as-button float-left">
                        <span class="icon icon-pencil icon-lg" role="presentation" aria-hidden="true"></span>
                        <span class="sr-only">{str tag=edit}</span>
                    </a>
                    {/if}
                {/if}
                {if $item->deleteform}
                    {$item->deleteform|safe}
                {/if}
                {if $item->canreply}
                <button class="btn btn-secondary float-left commentreplyto btn-group-item js-reply" id="commentreplyto{$item->id}" title="{str tag=reply section=artefact.comment}" data-replyto="{$item->id}" data-canprivatereply="{$item->canprivatereply}" data-canpublicreply="{$item->canpublicreply}">
                    <span class="icon icon-reply icon-lg" role="presentation" aria-hidden="true"></span>
                    <span class="sr-only">{str tag=reply section=artefact.comment}</span>
                </button>
                {/if}
            </div>
        </div>
        <div class="comment-text">
            <div class="comment-content">
                {if $item->deletedmessage}
                    <span class="metadata">
                        {$item->deletedmessage}
                    </span>
                {else}
                    {if $item->author}
                        {$item->description|safe|clean_html}
                    {else}
                        {$item->description|safe}
                    {/if}
                {/if}
            </div>

            {if $item->makepublicform || ($item->makepublicrequested && !$item->deletedmessage)}
            <div class="metadata">
                {if $item->pubmessage}
                <em class="privatemessage"> {$item->pubmessage}
                </em> -
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
        <div class="comment-attachment">
            <div class="card has-attachment collapsible">
                <h4 class="card-header">
                    <a class="collapsible collapsed" aria-expanded="false" href="#attachments_{$item->id}" data-toggle="collapse">
                        <span class="icon left icon-paperclip" role="presentation" aria-hidden="true"></span>
                        <span class="text-small">{str tag=Attachments section=artefact.comment} ({$item->filescount})</span>
                        <span class="icon icon-chevron-down float-right collapse-indicator" role="presentation" aria-hidden="true"></span>
                    </a>
                </h4>
                <div id="attachments_{$item->id}" class="collapse" aria-expanded="false">
                    <ul class=" list-group list-group-unbordered">
                    {strip}
                        {foreach $item->attachments item=a name=attachments}
                        <li class="list-group-item">
                            <a href="{$WWWROOT}artefact/file/download.php?file={$a->attachid}&comment={$item->id}&view={$viewid}" class="outer-link icon-on-hover">
                                <span class="sr-only">{$a->attachtitle}</span>
                            </a>
                            <span class="title">
                                {$a->attachtitle}
                                <span class="attachsize metadata">
                                    [{$a->attachsize}]
                                </span>
                            </span>
                            <span class="icon icon-download icon-lg float-right text-watermark icon-action" role="presentation" aria-hidden="true"></span>
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
{/foreach}
</div>
