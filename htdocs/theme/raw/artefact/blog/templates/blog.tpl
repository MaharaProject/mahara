<div class="panel-body">
    {if !$options.hidetitle}
    <h2>{$artefacttitle|safe}</h2>
    {/if}

    <div id="blogdescription" class="blogdescription ptl">{$description|clean_html|safe}
    {if $tags}
    <div class="tags pbl">
        <strong>{str tag=tags}:</strong> {list_tags owner=$owner tags=$tags}
    </div>
        {/if}
    </div>

    <div id="postlist{if $blockid}_{$blockid}{/if}" class="postlist fullwidth">
      {$posts.tablerows|safe}
    </div>
    {if $posts.pagination}
    <div id="blogpost_page_container{if $blockid}_{$blockid}{/if}" class="hidden center">{$posts.pagination|safe}</div>
    {/if}
    {if $license}
      <div class="bloglicense">
        {$license|safe}
      </div>
    {/if}

    {if $posts.pagination_js}
    <script>
    addLoadEvent(function() {literal}{{/literal}
        {$posts.pagination_js|safe}
        removeElementClass('blogpost_page_container{if $blockid}_{$blockid}{/if}', 'hidden');
    {literal}}{/literal});
    </script>
    {/if}

      {if ($editing)}
        <a class="panel-footer" href="{$WWWROOT}artefact/blog/post.php?blog={$blogid}" target="_blank">
            <span class="fa fa-plus text-success prs"></span> 
            {str tag='shortcutnewentry' section='artefact.blog'}
        </a>
    {/if}
</div>

