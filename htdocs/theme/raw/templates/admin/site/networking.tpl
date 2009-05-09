{include file='header.tpl'}
{include file="columnfullstart.tpl"}
<h2>{str section=admin tag=networking}</h2>

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
{include file="columnfullend.tpl"}
{include file='footer.tpl'}
