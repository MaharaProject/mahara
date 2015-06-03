<div class="panel-body">
{if $profileiconpath}
    <div class="user-icon pull-right mll">
        <img src="{$profileiconpath}" alt="{$profileiconalt}" />
    </div>
{/if}

{if $profileinfo && $profileinfo.introduction}
    {$profileinfo.introduction|clean_html|safe}
{/if}
{if $profileinfo && (count($profileinfo) != 1 || !$profileinfo.introduction || !$profileinfo.socialprofiles)}
    <ul class="unstyled">
        {foreach from=$profileinfo key=key item=item}
            {if !in_array($key, array('introduction', 'socialprofiles'))}
                <li><strong>{str tag=$key section=artefact.internal}:</strong> {$item|clean_html|safe}</li>
            {/if}
        {/foreach}
    </ul>
{/if}

{if $profileinfo.socialprofiles}
    <h4 class="sr-only">{str tag=socialprofiles section=artefact.internal}</h4>
    <ul class="unstyled mtm">
        {foreach from=$profileinfo.socialprofiles item=item}
            <li><strong>{$item.description}:</strong>
                {if $item.link}<a href="{$item.link}" title="{$item.link}" target="_blank">{/if}{$item.title|clean_html|safe}{if $item.link}</a>{/if}
            </li>
        {/foreach}
    </ul>
{/if}

</div>