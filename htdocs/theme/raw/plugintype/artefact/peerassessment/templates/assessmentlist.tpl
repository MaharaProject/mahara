<!-- The "feedbacktable" class is used as an identifier by Javascript -->
<div class="list-group list-group-lite">
{foreach from=$data item=item}
    <div id="assessment{$item->id}" class="comment-item list-group-item {cycle name=rows values='r0,r1'} {if $item->attachments}has-attachment{/if} {if $item->private}draft{/if}">
        <div class="usericon-heading">
            <span class="user-icon pull-left" role="presentation" aria-hidden="true">
                {if $item->author && !$item->author->deleted}
                    <img src="{profile_icon_url user=$item->author maxheight=40 maxwidth=40}" valign="middle" alt="{str tag=profileimagetext arg1=$item->author|display_default_name}"/>
                {else}
                    <img src="{profile_icon_url user=null maxheight=40 maxwidth=40}" valign="middle" alt="{str tag=profileimagetextanonymous}"/>
                {/if}
            </span>
            <h5 class="pull-left list-group-item-heading">
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
            </h5>
            <!-- The "assessment-item-buttons" class is used as an identifier by Javascript -->
            <div class="btn-group float-right assessment-item-buttons">
                {if $item->editlink}
                    {$item->editlink|safe}
                {/if}
                {if $item->deleteform}
                    {$item->deleteform|safe}
                {/if}
            </div>
        </div>
        <div class="comment-text">
            <div class="comment-content">
                {if $item->author}
                    {$item->description|safe|clean_html}
                {else}
                    {$item->description|safe}
                {/if}
            </div>
        </div>
    </div>
{/foreach}
</div>
