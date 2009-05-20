{if $profileiconpath}<div class="fr"><img src="{$profileiconpath|escape}" alt=""></div>{/if}
<p>{$profileinfo.introduction|clean_html}</p>

<ul>
{foreach from=$profileinfo key=key item=item}
{if in_array($key, array('introduction'))}
    {* Skip some fields *}
{else}
    <li><strong>{str tag=$key section=artefact.internal}:</strong> {$item}</li>
{/if}
{/foreach}
</ul>

{if $profileiconpath}<div class="cb"></div>{/if}
