{if $plans}
<ul>
{foreach from=$plans item=plan}
    <li><a href="{$plan.link}">{$plan.title}</a></li>
{/foreach}
</ul>
{/if}
