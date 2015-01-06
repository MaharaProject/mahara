{include file="export:html:header.tpl"}

{if $icon}<div id="profile-icon">{$icon|safe}</div>{/if}

{foreach from=$sections key=sectionname item=section}
{if count($section)}
<div class="profileinfo">
    <h3>{str tag=$sectionname section=artefact.internal}</h3>
    <table>
{foreach from=$section key=title item=value}
    {if $title == 'socialprofile'}
        {foreach from=$value item=profile}
        <tr>
            <th>{$profile.label|safe}:</th>
            <td>{$profile.link|safe}</td>
        </tr>
        {/foreach}
    {else}
    <tr>
        <th>{str tag=$title section=artefact.internal}:</th>
        <td>{$value|safe}</td>
    </tr>
    {/if}
{/foreach}
    </table>
</div>
{/if}
{/foreach}

{include file="export:html:footer.tpl"}
