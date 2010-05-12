{auto_escape off}
<p>{$stryouhaveblogs}</p>
{if $blogs}
<ul>
{foreach from=$blogs item=blog}
    <li><a href="{$blog.link|escape}">{$blog.title|escape}</a></li>
{/foreach}
</ul>
{/if}
{/auto_escape}
