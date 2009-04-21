{include file="export:html:header.tpl"}

<h2>Homepage of exported data</h2>

{foreach from=$summaries item=summary}
<div>
    <h3>{$summary.title|escape}</h3>
    {$summary.description}
</div>
{/foreach}

{include file="export:html:footer.tpl"}
