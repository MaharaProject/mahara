{auto_escape off}
{foreach from=$categories item=category}        <category term="{$category.term|escape}"{if $category.scheme} scheme="categories:{$category.scheme|escape}#"{/if}{if $category.label && $category.label != $category.term} label="{$category.label|escape}"{/if}/>
{/foreach}
{/auto_escape}
