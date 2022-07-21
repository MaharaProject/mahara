{if $list}
<p>{$stryouhaveviews}</p>
<ul>
{foreach from=$list item=item}
  {if $item.views}
    {foreach from=$item.views item=view}
        <li><a href="views/{$item.id}_{$view.folder}/index.html">{$view.title}</a></li>
    {/foreach}
  {else}
    <li><a href="views/{$item.folder}/index.html">{$item.title}</a></li>
  {/if}
{/foreach}
</ul>
{else}
<p>{str tag=youhavenoviews2 section=view}</p>
{/if}
