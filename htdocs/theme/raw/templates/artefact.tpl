<h2>{$title}</h2>
{if $tags}<p class="tags s"><label>{str tag=tags}:</label> {list_tags owner=$owner tags=$tags}</p>{/if}
<p>{$description|clean_html|safe}</p>
{if $license}
  <div class="artefactlicense">
    {$license|safe}
  </div>
{/if}
