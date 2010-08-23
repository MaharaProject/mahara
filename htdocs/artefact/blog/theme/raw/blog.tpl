{if !$options.hidetitle}
<h2>{$artefacttitle|safe}</h2>
{/if}

{$description|clean_html|safe}
{if $tags}<div class="tags"><label>{str tag=tags}:</label> {list_tags owner=$owner tags=$tags}</div>{/if}

<table id="postlist_{$blockid}" class="postlist">
  <tbody>
  {$posts.tablerows|safe}
  </tbody>
</table>
{if $posts.pagination}
<div id="blogpost_page_container_{$blockid}" class="hidden center">{$posts.pagination|safe}</div>
{/if}
{if $posts.pagination_js}
<script>
addLoadEvent(function() {literal}{{/literal}
    {$posts.pagination_js|safe}
    removeElementClass('blogpost_page_container_{$blockid}', 'hidden');
{literal}}{/literal});
</script>
{/if}
