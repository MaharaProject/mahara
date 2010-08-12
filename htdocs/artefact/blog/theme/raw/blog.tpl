{if !$options.hidetitle}
<h2>{$artefacttitle|safe}</h2>
{/if}

{$description|clean_html|safe}
{if $tags}<div class="tags">{str tag=tags}: {list_tags owner=$owner tags=$tags}</div>{/if}

<table id="postlist_{$blockid}" class="postlist">
  <tbody>
  {$posts.tablerows|safe}
  </tbody>
</table>
<div id="blogpost_page_container_{$blockid}" class="hidden center">{$posts.pagination|safe}</div>
<script>
addLoadEvent(function() {literal}{{/literal}
    {$posts.pagination_js|safe}
    removeElementClass('blogpost_page_container_{$blockid}', 'hidden');
{literal}}{/literal});
</script>
