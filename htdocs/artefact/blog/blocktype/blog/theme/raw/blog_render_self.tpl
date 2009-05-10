{if !$options.hidetitle}<h2>{$artefacttitle}</h2>{/if}

{$description}

{foreach from=$postdata item=post}
{$post.content.html}
{/foreach}

{if $newerpostslink || $olderpostslink}
<div class="blog-pagination">
{if $olderpostslink}<div class="fr"><a href="{$olderpostslink|escape}">{str tag=olderposts section=artefact.blog}</a></div>{/if}
{if $newerpostslink}<div><a href="{$newerpostslink|escape}">{str tag=newerposts section=artefact.blog}</a></div>{/if}
</div>
{/if}
