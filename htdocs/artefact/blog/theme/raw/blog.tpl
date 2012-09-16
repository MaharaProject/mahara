{if ($editing)}
<div class="right">
    <span> <a class="btn" href="{$WWWROOT}artefact/blog/post.php?blog={$blogid}" target="_blank">{str tag='shortcutnewentry' section='artefact.blog'}</a> </span>
</div>
{/if}
{if !$options.hidetitle}
<h2>{$artefacttitle|safe}</h2>
{/if}

<div id="blogdescription">{$description|clean_html|safe}
{if $tags}<p class="tags s"><label>{str tag=tags}:</label> {list_tags owner=$owner tags=$tags}</p>{/if}
</div>
<table id="postlist{if $blockid}_{$blockid}{/if}" class="postlist">
  <tbody>
  {$posts.tablerows|safe}
  </tbody>
</table>
{if $posts.pagination}
<div id="blogpost_page_container{if $blockid}_{$blockid}{/if}" class="hidden center">{$posts.pagination|safe}</div>
{/if}
{if $posts.pagination_js}
<script>
addLoadEvent(function() {literal}{{/literal}
    {$posts.pagination_js|safe}
    removeElementClass('blogpost_page_container{if $blockid}_{$blockid}{/if}', 'hidden');
{literal}}{/literal});
</script>
{/if}
