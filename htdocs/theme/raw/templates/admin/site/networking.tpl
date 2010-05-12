{auto_escape off}
{include file='header.tpl'}

{if $missingextensions}
<p>{str section=admin tag=networkingextensionsmissing}</p>
<ul>
{foreach from=$missingextensions item=extension}
    <li><a href="http://www.php.net/{$extension|escape}">{$extension|escape}</a></li>
{/foreach}
</ul>
{else}
<p>{str tag=networkingpagedescription section=admin}</p>
{$networkingform}
{/if}

{include file='footer.tpl'}
{/auto_escape}
