<p>{$stryouhaveviews}</p>
<ul>
{foreach from=$list item=item}
  {if $item.views}
    {foreach from=$item.views item=view}
        <li><a href="views/{$view.folder}/index.html">{$view.title}</a></li>
    {/foreach}
  {/if}
{/foreach}
</ul>
