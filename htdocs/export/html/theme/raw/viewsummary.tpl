<p>{$stryouhaveviews}</p>
<ul>
{foreach from=$list item=item}
  {if $item.folder}
    <li><a href="views/{$item.folder}/index.html">{$item.title}</a></li>
  {elseif $item.views}
    <li>{$item.title}
      <ul>
    {foreach from=$item.views item=view}
        <li><a href="views/{$view.folder}/index.html">{$view.title}</a></li>
    {/foreach}
      </ul>
    </li>
  {/if}
{/foreach}
</ul>
