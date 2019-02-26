<div class="blog">
    {if $alldraftposts}
        {str tag='alldraftposts' section='artefact.blog'}
    {/if}
    {if ($editing && $canaddpost)}
        <a class="card-footer" href="{$WWWROOT}artefact/blog/post.php?blog={$blogid}">
            <span class="icon icon-plus text-success left" role="presentation" aria-hidden="true"></span>
            {str tag='shortcutnewentry' section='artefact.blog'}
        </a>
    {/if}
    {if !$options.hidetitle}
    <h2>{$artefacttitle|safe}</h2>
    {/if}

    <div id="blogdescription" class="content-text">{$description|clean_html|safe}
    {if $tags}
    <div class="tags">
        <strong>{str tag=tags}:</strong> {list_tags owner=$owner tags=$tags view=$view}
    </div>
    {/if}
    </div>

    {if $posts.tablerows}
        <div id="postlist{if $blockid}_{$blockid}{/if}" class="fullwidth list-group">
          {$posts.tablerows|safe}
        </div>
        {if $posts.pagination}
        <div id="blogpost_page_container{if $blockid}_{$blockid}{/if}" class="d-none">{$posts.pagination|safe}</div>
        {/if}
        {if $license}
          <div class="license">
            {$license|safe}
          </div>
        {/if}
    {/if}
    {if $posts.pagination_js}
    <script>
    jQuery(function($) {literal}{{/literal}
        {$posts.pagination_js|safe}
        $('#blogpost_page_container{if $blockid}_{$blockid}{/if}').removeClass('d-none');
    {literal}}{/literal});
    </script>
    {/if}
</div>
