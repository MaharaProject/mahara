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
        <a class="link-unstyled" href="{$WWWROOT}artefact/blog/view/index.php?id={$blog->id}">
        {$blog->description|clean_html|safe}
        </a>
    </div>

    <div class="panel-footer has-form">
        <a href="{$WWWROOT}artefact/blog/post.php?blog={$blog->id}" class="btn btn-default btn-sm">
            <span class="fa fa-plus text-primary mrs"></span>
            {str tag=addpost section=artefact.blog}
        </a>
        <div class="pull-right">
            {if $blog->locked}
                {str tag=submittedforassessment section=view}
            {else}
            <a href="{$WWWROOT}artefact/blog/settings/index.php?id={$blog->id}" title="{str(tag=settingsspecific arg1=$blog->title)|escape:html|safe}" class="btn btn-default btn-sm">
                <span class="fa fa-pencil mrs"></span>
                {str tag=edit}
            </a>
            <span class="control">
            {$blog->deleteform|safe}
            </span>
            {/if}
        </div>
    </div>
</div>
{/foreach}
