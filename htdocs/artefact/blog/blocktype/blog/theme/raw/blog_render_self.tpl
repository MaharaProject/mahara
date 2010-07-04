{if !$options.hidetitle}<h2>{$artefacttitle|safe}</h2>{/if}

{$description|clean_html|safe}
{if $tags}<div class="tags">{str tag=tags}: {list_tags owner=$owner tags=$tags}</div>{/if}

{foreach from=$postdata item=post}
{$post.content.html|safe}
{/foreach}

{if $newerpostslink || $olderpostslink}
<div class="blog-pagination">
{if $olderpostslink}<div class="fr"><a href="{$olderpostslink}">{str tag=olderposts section=artefact.blog}</a></div>{/if}
{if $newerpostslink}<div><a href="{$newerpostslink}">{str tag=newerposts section=artefact.blog}</a></div>{/if}
</div>
{/if}
