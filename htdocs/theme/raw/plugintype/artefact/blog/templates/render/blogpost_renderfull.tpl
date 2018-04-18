{**
* This template displays a blog post.
*}
{if $published}
<div id="blogpost-{$postid}" class="card-body flush">

    {if $artefacttitle && $simpledisplay}
    <h2 class="title">
        {$artefacttitle|safe}
    </h2>
    {/if}

    <div class="postdetails metadata">
        <span class="icon icon-calendar left" role="presentation" aria-hidden="true"></span>
        {$postedbyon}
        {if $updatedon}
            <br>
            <span class="icon icon-calendar left" role="presentation" aria-hidden="true"></span>
            {$updatedon}
        {/if}
    </div>

    {if $artefacttags}
    <div class="tags metadata">
        <span class="icon icon-tags" role="presentation" aria-hidden="true"></span>
        <strong>{str tag=tags}:</strong>
        {list_tags owner=$artefactowner tags=$artefacttags view=$artefactview}
    </div>
    {/if}

    <div class="postcontent">
    {$artefactdescription|clean_html|safe}
    </div>

    {if $license}
    <div class="license">
        {$license|safe}
    </div>
    {/if}

    {if isset($attachments)}
        {include file="artefact:blog:render/blogpost_renderattachments.tpl" attachments=$attachments postid=$postid}
    {/if}
</div>
{else}
<div>
{$notpublishedblogpost|safe}
</div>
{/if}
