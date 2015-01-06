{include file='header.tpl'}

{if $missingextensions}
<p>{str section=admin tag=networkingextensionsmissing}</p>
<ul>
{foreach from=$missingextensions item=extension}
    <li><a href="http://www.php.net/{$extension}">{$extension}</a></li>
{/foreach}
</ul>
{else}
<p>{str tag=networkingpagedescription section=admin}</p>
{$networkingform|safe}
{/if}

{include file='footer.tpl'}