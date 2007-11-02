<ul>
{foreach from=$profileinfo key=key item=item}
    <li><strong>{str tag=$key section=artefact.internal}:</strong>
{if $key == 'email'}
    <a href="mailto:{$item|escape}">{$item|escape}</a>
{elseif $key == 'country'}
    {str tag=country.$item}
{elseif in_array($key, array('officialwebsite', 'personalwebsite', 'blogaddress'))}
    <a href="{$item|escape}">{$item|str_shorten:50}</a>
{else}
    {$item|escape}
{/if}</li>
{/foreach}
</ul>

