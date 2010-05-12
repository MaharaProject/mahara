{auto_escape off}
<ul>
{foreach from=$profileinfo key=key item=item}
    <li><strong>{str tag=$key section=artefact.internal}:</strong> {$item}</li>
{/foreach}
</ul>

{/auto_escape}
