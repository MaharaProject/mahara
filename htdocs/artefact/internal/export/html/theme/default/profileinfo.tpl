{include file="export:html:header.tpl"}

{foreach from=$sections key=sectionname item=section}
<div>
    <h3>{$sectionname|escape}</h3>
    <dl>
{foreach from=$section key=title item=value}
        <dt>{$title|escape}</dt>
        <dd>{$value}</dd>
{/foreach}
    </dl>
{/foreach}

{include file="export:html:footer.tpl"}
