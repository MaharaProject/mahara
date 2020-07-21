{if $profileinfo}
    {if !$profileinfo.nodata}
    <div class="card-body flush">
    {/if}
    {if $profileiconpath}
        <div class="user-icon float-right user-icon-60">
            <img src="{$profileiconpath}" alt="{$profileiconalt}" />
        </div>
    {/if}
    {if $profileinfo.introduction}
        {$profileinfo.introduction|clean_html|safe}
    {/if}
    {if is_array($profileinfo.internalprofiles) && count($profileinfo.internalprofiles) > 0}
        <ul class="unstyled profile-info">
        {foreach from=$profileinfo.internalprofiles key=key item=item}
            <li><strong>{get_desired_field_title($item.type)}:</strong> {$item.description|clean_html|safe}</li>
        {/foreach}
        </ul>
    {/if}
    {if $profileinfo.socialprofiles}
        <h3 class="sr-only">{str tag=socialprofiles section=artefact.internal}</h3>
        <ul class="unstyled profile-info">
        {foreach from=$profileinfo.socialprofiles item=item}
            <li><strong>{$item.description}:</strong>
                {if $item.link}<a href="{$item.link}" title="{$item.link}">{/if}{$item.title|clean_html|safe}{if $item.link}</a>{/if}
            </li>
        {/foreach}
        </ul>
    {/if}
    {if $profileinfo.nodata}
        <p class="editor-description">{$profileinfo.nodata}</p>
    {/if}
    {if !$profileinfo.nodata}
    </div>
    {/if}
{/if}
