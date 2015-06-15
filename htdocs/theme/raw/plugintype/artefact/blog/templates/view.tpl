{include file="header.tpl"}
<div class="text-right btn-top-right btn-group btn-group-top">
    <a class="btn btn-default addpost" href="{$WWWROOT}artefact/blog/post.php?blog={$blog->get('id')}">
        <span class="icon icon-lg icon-plus prs text-success"></span>
        {str section="artefact.blog" tag="addpost"}
    </a>
    {if !$blog->get('locked')}
    <a class="btn btn-default settings" href="{$WWWROOT}artefact/blog/settings/index.php?id={$blog->get('id')}">
        <span class="icon icon-lg icon-cogs prs"></span>
        {str section="artefact.blog" tag="settings"}
    </a>
    {/if}
</div>
<div id="myblogs" class="rel ptxl mtxl">
    <div id="blogdescription" class="blogdescription">
        {clean_html($blog->get('description'))|safe}
    </div>
    {if $blog->get('tags')}
    <div class="tags">
        <strong>{str tag=tags}:</strong> {list_tags owner=$blog->get('owner') tags=$blog->get('tags')}
    </div>
    {/if}

    {if $posts}
    <div id="postlist" class="ptl list-group list-group-lite">
        {$posts.tablerows|safe}
    </div>
    <div id="blogpost_page_container" class="hidden">{$posts.pagination|safe}</div>
    <script>
    addLoadEvent(function() {literal}{{/literal}
        {$posts.pagination_js|safe}
        removeElementClass('blogpost_page_container', 'hidden');
        {literal}}{/literal});
    </script>
    {else}
    <div class="metadata">
        {str tag=nopostsyet section=artefact.blog} {if !$blog->get('locked')}<a href="{$WWWROOT}artefact/blog/post.php?blog={$blog->get('id')}">{str tag=addone section=mahara}</a>{/if}
    </div>
    {/if}

    {if $enablemultipleblogstext}
    <p class="alert alert-default mtl">
        {str tag=enablemultipleblogstext section=artefact.blog arg1=$WWWROOT}</p>
    {/if}
    
    {if $hiddenblogsnotification}
    <p class="lead text-center ptl">
        {str tag=hiddenblogsnotification section=artefact.blog arg1=$WWWROOT}</p>
    {/if}
</div>
{include file="footer.tpl"}

