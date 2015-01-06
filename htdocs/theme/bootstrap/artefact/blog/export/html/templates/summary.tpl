<p>{$stryouhaveblogs}</p>
{if $blogs}
<ul>
{foreach from=$blogs item=blog}
    <li><a href="{$blog.link}">{$blog.title}</a></li>
{/foreach}
</ul>
{/if}
