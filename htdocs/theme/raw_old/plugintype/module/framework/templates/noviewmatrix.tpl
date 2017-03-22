{include file="header.tpl"}

<h1 id="viewh1" class="page-header">
    <span class="section-heading">{$name}</span>
</h1>
<div class="with-heading text-small">
    {include file=author.tpl}
</div>
<div>
    {$error|safe}
    {if $firstviewlink}
        {$firstviewlink|safe}
    {/if}
</div>
{include file="footer.tpl"}