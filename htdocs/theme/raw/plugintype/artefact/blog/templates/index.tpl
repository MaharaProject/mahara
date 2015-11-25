{include file="header.tpl"}
<div class="btn-top-right btn-group btn-group-top">
    <a class="btn btn-default settings" href="{$WWWROOT}artefact/blog/new/index.php{if $institutionname}?institution={$institutionname}{/if}{if $group}?group={$group}{/if}">
        <span class="icon icon-lg icon-plus left"></span>
        {str section="artefact.blog" tag="addblog"}
    </a>
</div>
{if !$blogs->data}
<p class="no-results">{str tag=youhavenoblogs section=artefact.blog}</p>
{else}
<div id="bloglist" class="rel view-container">
    <div class="panel-items js-masonry" data-masonry-options='{ "itemSelector": ".panel" }'>
        {$blogs->tablerows|safe}
    </div>
    {$blogs->pagination|safe}
</div>
{/if}
{include file="footer.tpl"}
