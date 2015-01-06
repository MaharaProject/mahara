{if $profileiconpath}<div class="fr"><img src="{$profileiconpath}" alt="{$profileiconalt}"></div>{/if}
<p>{$profileinfo.introduction|clean_html|safe}</p>

{if $profileinfo && (count($profileinfo) != 1 || !$profileinfo.introduction || !$profileinfo.socialprofiles)}
    <ul>
        {foreach from=$profileinfo key=key item=item}
            {if !in_array($key, array('introduction', 'socialprofiles'))}
                <li><strong>{str tag=$key section=artefact.internal}:</strong> {$item|clean_html|safe}</li>
            {/if}
        {/foreach}
    </ul>
{/if}

{if $profileinfo.socialprofiles}
    <h4>{str tag=socialprofiles section=artefact.internal}</h4>
    <ul>
        {foreach from=$profileinfo.socialprofiles item=item}
            <li><strong>{$item.description}:</strong>
                {if $item.link}<a href="{$item.link}" title="{$item.link}" target="_blank">{/if}{$item.title|clean_html|safe}{if $item.link}</a>{/if}
            </li>
        {/foreach}
    </ul>
{/if}

{if $profileiconpath}<div class="cb"></div>{/if}
