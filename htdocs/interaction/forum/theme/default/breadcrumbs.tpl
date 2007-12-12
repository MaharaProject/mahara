<span>
{foreach from=$breadcrumbs item=item name=breadcrumbs}
<a href="{$item[0]|escape}">{$item[1]|escape}</a>
{if !$smarty.foreach.breadcrumbs.last} &raquo {/if}
{/foreach}
</span>