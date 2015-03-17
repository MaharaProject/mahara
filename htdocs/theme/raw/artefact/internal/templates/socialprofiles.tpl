<br /><div class="rbuttons">
    <a class="btn" href="{$WWWROOT}artefact/internal/socialprofile.php">{str tag=newsocialprofile section=artefact.internal}</a>
</div>
<table id="socialprofilelist" class="tablerenderer fullwidth">
    <thead>
        <tr>
            <th></th>
            <th>{str tag='service' section='artefact.internal'}</th>
            <th>{str tag='profileurl' section='artefact.internal'}</th>
            {if $controls}<th>
                <span class="accessible-hidden">{str tag=edit}</span>
            </th>{/if}
        </tr>
    </thead>
    <tbody>
        {foreach from=$rows item=row}
        <tr class="{cycle values='r0,r1'}">
            <td class="icon-container"><img src="{$row->icon}" alt="{$row->description}"></td>
            <td class="smdescription">{$row->description}</td>
            <td class="smlink">{if $row->link}<a href="{$row->link}" title="{$row->link}" target="_blank" class="socialprofile">{/if}{$row->title}{if $row->link}</a>{/if}</td>
            {if $controls}<td class="right buttonscell btns2">
                <a href="{$WWWROOT}artefact/internal/socialprofile.php?id={$row->id}" title="{str tag='edit'}"><img src="{theme_image_url filename='btn_edit'}" alt="{str tag='edit'}"></a>
                {if $candelete}
                <a href="{$WWWROOT}artefact/internal/socialprofile.php?id={$row->id}&delete=1" title="{str tag='delete'}"><img src="{theme_image_url filename='btn_deleteremove'}" alt="{str tag='delete'}"></a>
                {/if}
            </td>{/if}
        </tr>
        {/foreach}
    </tbody>
</table>
{$pagination.html|safe}


