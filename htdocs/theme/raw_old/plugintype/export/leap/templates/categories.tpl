{foreach from=$categories item=category}        <category term="{$category.term}"{if $category.scheme} scheme="categories:{$category.scheme}#"{/if}{if $category.label && $category.label != $category.term} label="{$category.label}"{/if}/>
{/foreach}
