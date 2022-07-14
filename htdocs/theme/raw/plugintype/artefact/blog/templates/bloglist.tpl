{foreach from=$blogs->data item=blog}
<div class="card {if $blog->locked}card bg-warning{else} card{/if} blog card-half">
    <h2 class="card-header has-link">
        <a class="title-link title autofocus" href="{$WWWROOT}artefact/blog/view/index.php?id={$blog->id}">
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
         <span class="icon icon-arrow-right float-end link-indicator" role="presentation" aria-hidden="true"></span>
        </a>
    </h2>

    <div id="blogdesc" class="card-body">
        <a class="link-unstyled" href="{$WWWROOT}artefact/blog/view/index.php?id={$blog->id}">
        {$blog->description|clean_html|safe}
        </a>
    </div>

    {if $blog->canedit}
    <div class="card-footer has-form">
        <button data-url="{$WWWROOT}artefact/blog/post.php?blog={$blog->id}" class="btn btn-secondary btn-sm">
            <span class="icon icon-plus left" role="presentation" aria-hidden="true"></span>
            <span class="visually-hidden">{str tag=addpostspecific arg1=$blog->title section=artefact.blog |escape:html|safe}</span>
            {str tag=addpost section=artefact.blog}
        </button>

        <div class="btn-group float-end">
            {if $blog->locked}
                <span class="text-small">{str tag=submittedforassessment section=view}</span>
            {else}
            <button data-url="{$WWWROOT}artefact/blog/settings/index.php?id={$blog->id}" type="button" title="{str(tag=settingsspecific arg1=$blog->title)|escape:html|safe}" class="btn btn-secondary btn-sm btn-group-item">
                <span class="icon icon-pencil-alt" role="presentation" aria-hidden="true"></span>
                <span class="visually-hidden">{str tag=editspecific arg1=$blog->title}</span>
            </button>
            {$blog->deleteform|safe}
            {/if}
        </div>
    </div>
    {/if}
</div>
{/foreach}
