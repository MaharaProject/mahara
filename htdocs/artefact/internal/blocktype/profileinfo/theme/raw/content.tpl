{if $profileiconpath}<div class="fr"><img src="{$profileiconpath|escape}" alt=""></div>{/if}
<p>{$profileinfo.introduction|clean_html}</p>
{if $profileinfo && (count($profileinfo) != 1 || !$profileinfo.introduction)}<ul>
{foreach from=$profileinfo key=key item=item}
{if !in_array($key, array('introduction'))}    <li><strong>{str tag=$key section=artefact.internal}:</strong> {$item}</li>
{/if}
{/foreach}
</ul>{/if}
{if $profileiconpath}<div class="cb"></div>{/if}
