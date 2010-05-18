<ul>
{foreach from=$profileinfo key=key item=item}
    <li><strong>{str tag=$key section=artefact.internal}:</strong> {$item|safe}</li>
{/foreach}
</ul>