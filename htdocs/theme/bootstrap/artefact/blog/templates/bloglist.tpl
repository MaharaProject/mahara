{foreach from=$blogs->data item=blog}
<div class="panel panel-default blog panel-half">
    <h3 class="panel-heading has-link">
        <a class="title-link" href="{$WWWROOT}artefact/blog/view/index.php?id={$blog->id}">

        {$blog->title}

        {if $blog->postcount == 0}
            <span class="metadata mls">
                {str tag=nopostsyet section=artefact.blog}
            </span>
        {else}
            <span class="metadata mls">
                {str tag=nposts section=artefact.blog arg1=$blog->postcount}
            </span>
        {/if}
         <span class="fa fa-arrow-right mrs pull-right link-indicator"></span>
        </a>
    </h3>

    <div id="blogdesc" class="panel-body">
        <a class="unstyled" href="{$WWWROOT}artefact/blog/view/index.php?id={$blog->id}">
        {$blog->description|clean_html|safe}
        </a>
    </div>

    <div class="panel-footer has-form">
        <a href="{$WWWROOT}artefact/blog/post.php?blog={$blog->id}" class="btn btn-primary btn-xs">
                <span class="fa fa-plus mrs"></span>
                {str tag=addpost section=artefact.blog}
            </a>
        <div class="pull-right">
            {if $blog->locked}
                {str tag=submittedforassessment section=view}
            {else}
            <a href="{$WWWROOT}artefact/blog/settings/index.php?id={$blog->id}" title="{str tag=settings}" class="btn btn-default btn-xs">
                <span class="fa fa-pencil"></span>
                <span class="sr-only">
                    {str(tag=settingsspecific arg1=$blog->title)|escape:html|safe}
                </span>
            </a>
            <span class="control">
            {$blog->deleteform|safe}
            </span>
            {/if}
        </div>
    </div>
</div>
{/foreach}
