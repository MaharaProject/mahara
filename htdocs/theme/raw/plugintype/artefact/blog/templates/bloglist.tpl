{foreach from=$blogs->data item=blog}
<div class="card {if $blog->locked}card bg-warning{else} card{/if} blog card-half">
    <h3 class="card-header has-link">
        <a class="title-link title" href="{$WWWROOT}artefact/blog/view/index.php?id={$blog->id}">
        {$blog->title}
        {if $blog->postcount == 0}
            <span class="metadata post-count">
                {str tag=nopostsyet section=artefact.blog}
            </span>
        {else}
            <span class="metadata post-count">
                {str tag=nposts section=artefact.blog arg1=$blog->postcount}
            </span>
        {/if}
         <span class="icon icon-arrow-right float-right link-indicator" role="presentation" aria-hidden="true"></span>
        </a>
    </h3>

    <div id="blogdesc" class="card-body">
        <a class="link-unstyled" href="{$WWWROOT}artefact/blog/view/index.php?id={$blog->id}">
        {$blog->description|clean_html|safe}
        </a>
    </div>

    {if $blog->canedit}
    <div class="card-footer has-form">
        <a href="{$WWWROOT}artefact/blog/post.php?blog={$blog->id}" class="btn btn-secondary btn-sm">
            <span class="icon icon-plus icon-lg left" role="presentation" aria-hidden="true"></span>
            <span class="sr-only">{str tag=addpostspecific arg1=$blog->title section=artefact.blog |escape:html|safe}</span>
            {str tag=addpost section=artefact.blog}
        </a>

        <div class="btn-group float-right">
            {if $blog->locked}
                <span class="text-small">{str tag=submittedforassessment section=view}</span>
            {else}
            <a href="{$WWWROOT}artefact/blog/settings/index.php?id={$blog->id}" title="{str(tag=settingsspecific arg1=$blog->title)|escape:html|safe}" class="btn btn-secondary btn-sm btn-group-item">
                <span class="icon icon-pencil icon-lg" role="presentation" aria-hidden="true"></span>
                <span class="sr-only">{str tag=editspecific arg1=$blog->title}</span>
            </a>
            {$blog->deleteform|safe}
            {/if}
        </div>
    </div>
    {/if}
</div>
{/foreach}
