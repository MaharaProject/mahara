
{foreach from=$data item=item}
    <div class="panel panel-small {if $item->pubmessage}panel-warning{elseif $item->deletedmessage}panel-danger{else}panel-default{/if} {cycle name=rows values='r0,r1'}{if $item->highlight} highlight{/if}{if $item->makepublicform} private{/if}">
        <div class="panel-heading has-link">
            <h4>
                {if $item->author}
                    <a href="{$item->author->profileurl}" class="userinfo has-user-icon">
                {/if}
                    <span class="user-icon small-icon left">
                        {if $item->author}
                            <img src="{profile_icon_url user=$item->author maxheight=40 maxwidth=40}" valign="middle" alt="{str tag=profileimagetext arg1=$item->author|display_default_name}" />
                        {else}
                            <img src="{profile_icon_url user=null maxheight=40 maxwidth=40}" alt="{str tag=profileimagetextanonymous}" />
                        {/if}
                    </span>
                    {if $item->author}
                        {$item->author|display_name}
                    {/if}
                    <span class="postedon metadata">
                        - {$item->date} {if $item->updated}[{str tag=Updated}: {$item->updated}]{/if}

                        {if $item->pubmessage} - <em class="privatemessage"> {$item->pubmessage}</em>{/if}
                    </span>
                {if $item->author}
                    </a>
                {/if}
            </h4>

            {if !$onview}
            <span class="panel-control panel-header-form-actions">
                {if $item->deleteform}
                    {$item->deleteform|safe}
                {/if}
                {if $item->canedit}
                    <form class="form-as-button pull-left" name="edit_{$item->id}" action="{$WWWROOT}artefact/comment/edit.php">
                        <input type="hidden" name="id" value="{$item->id}">
                        <input type="hidden" name="view" value="{$viewid}">
                        <button class="btn btn-link btn-sm button">
                            <span class="fa fa-lg fa-pencil text-default"></span>
                            <span class="sr-only">{str tag=edit}</span>
                        </button>
                    </form>
                {/if}
            </span>
            {/if}
        </div>
        <div class="comment panel-body">
            {if $item->deletedmessage}
                <span class="text-danger text-small">{$item->deletedmessage}</span>
            {else}
                {if $item->ratingdata}
                <div class="star-comment-rating">
                    {for i $item->ratingdata->min_rating $item->ratingdata->max_rating}
                        {if !$item->ratingdata->export}
                    <input name="star{$item->id}" type="radio" class="star" {if $i === $item->ratingdata->value} checked="checked" {/if} disabled="disabled" />
                        {else}
                    <div class="star-rating star star-rating-applied star-rating-readonly{if $i <= $item->ratingdata->value} star-rating-on{/if}"><a>&nbsp;</a></div>
                        {/if}
                    {/for}
                </div>
                {/if}
                <div class="detail ptm">
                    {$item->description|safe|clean_html}
                </div>

            {/if}

            {if $item->makepublicform || ($item->makepublicrequested && !$item->deletedmessage)}
            <div class="text-right ptm">
                {if $item->makepublicform}
                    {$item->makepublicform|safe}
                {/if}

                {if $item->makepublicrequested && !$item->deletedmessage}
                    <span class="fa fa-lock text-default prs"></span>
                    <span class="metadata">{str tag=youhaverequestedpublic section=artefact.comment}</span>
                {/if}
            </div>
            {/if}
        </div>

        {if !$item->deletedmessage && $item->attachments}
            <a class="collapsible collapsed panel-footer" aria-expanded="false" href="#attachments_{$item->id}" data-toggle="collapse">
                <p class="text-left">
                    <span class="fa fa-lg prm fa-paperclip"></span>
                    <span class="text-small">{str tag=Attachments section=artefact.comment}</span>
                    <span class="fa fa-chevron-down pull-right collapse-indicator"></span>
                    {if $item->attachmessage}
                        <em class="attachmessage metadata"> - {$item->attachmessage}</em>
                    {/if}
                </p>
            </a>
            <div id="attachments_{$item->id}" class="collapse" aria-expanded="false">
                <ul class="list-unstyled list-group mb0">
                {strip}
                    {foreach $item->attachments item=a name=attachments}
                    <li class="list-group-item-text list-group-item-link">
                        <a href="{$WWWROOT}artefact/file/download.php?file={$a->attachid}&comment={$item->id}&view={$viewid}">{$a->attachtitle} <span class="attachsize metadata">[{$a->attachsize}]</span></a>
                    </li>
                    {/foreach}
                {/strip}
                </ul>
            </div>
        {/if}
    </div>
{/foreach}
