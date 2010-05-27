<p>{$stryouhaveviews}</p>
<ul>
{foreach from=$views item=view}
    <li><a href="views/{$view.folder}/index.html">{$view.title}</a></li>
{/foreach}
</ul>
