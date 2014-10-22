{include file="export:html:header.tpl"}

{foreach from=$summaries key=plugin item=summary}
<div class="summary {cycle values="odd,even"}" id="summary-{$plugin}">
    {if $summary.title}<h2>{$summary.title}</h2>{/if}
    {$summary.description|safe}
</div>
{/foreach}

{include file="export:html:footer.tpl"}
