<p>You have {$viewcount} views</p>
<ul>
{foreach from=$views item=view}
    <li><a href="views/{$view.folder|escape}/index.html">{$view.title|escape}</a></li>
{/foreach}
</ul>
