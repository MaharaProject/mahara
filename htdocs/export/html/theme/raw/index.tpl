{auto_escape off}
{include file="export:html:header.tpl"}

{foreach from=$summaries key=plugin item=summary}
<div class="summary {cycle values="odd,even"}" id="summary-{$plugin|escape}">
    {if $summary.title}<h2>{$summary.title|escape}</h2>{/if}
    {$summary.description}
</div>
{/foreach}

{include file="export:html:footer.tpl"}
{/auto_escape}
