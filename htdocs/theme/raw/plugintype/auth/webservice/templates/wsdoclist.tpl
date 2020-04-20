{if !$dialog}
{include file='header.tpl'}
{/if}

{foreach from=$functionlist item=sectionlist key=section}
<div class="wssection">
    <h3>{$section}</h3>
    <ul>
    {foreach from=$sectionlist item=method}
    <li>
        <a class="x" href="{$WWWROOT}webservice/wsdoc.php?functionname={$method->name}">{$method->methodname}</a>
    </li>
    {/foreach}
    </ul>
</div>
{/foreach}
{if !$dialog}
{include file='footer.tpl'}
{/if}