{if $rows}
<div class="text-end">
    <button class="btn btn-secondary" type="submit" data-url="{$WWWROOT}artefact/internal/socialprofile.php">
        <span class="icon icon-plus left" role="presentation" aria-hidden="true"></span>
        {str tag=newsocialprofile section=artefact.internal}
    </button>
</div>
<div class="table-responsive">
<table id="socialprofilelist" class="tablerenderer fullwidth table">
    <thead>
        <tr>
            <th class="icons"></th>
            <th>{str tag='socialprofile' section='artefact.internal'}</th>
            <th>{str tag='profileurl' section='artefact.internal'}</th>
            {if $controls}<th class="control-buttons">
                <span class="accessible-hidden visually-hidden">{str tag=edit}</span>
            </th>{/if}
        </tr>
    </thead>
    <tbody>
        {foreach from=$rows item=row}
        <tr class="social-info">
            <td class="text-center">
                {if $row->icon}
                    <img src="{$row->icon}" alt="{$row->description}">
                {else}
                    {$row->faicon|safe}
                {/if}
            </td>
            <td>
                <span>{$row->description}</span>
            </td>
            <td>
                {if $row->link}
                <a href="{$row->link}" title="{$row->link}" class="socialprofile">
                {/if}
                {$row->title}
                {if $row->link}
                </a>{/if}
            </td>
            {if $controls}
            <td class="control-buttons">
                <div class="btn-group">
                    <button data-url="{$WWWROOT}artefact/internal/socialprofile.php?id={$row->id}" type="button" title="{str tag='edit'}" class="btn btn-secondary btn-sm">
                        <span class="icon icon-pencil-alt" role="presentation" aria-hidden="true"></span>
                        <span class="visually-hidden">{str tag='edit'}</span>
                    </button>
                    {if $candelete}
                    <button data-url="{$WWWROOT}artefact/internal/socialprofile.php?id={$row->id}&delete=1" type="button" title="{str tag='delete'}" class="btn btn-secondary btn-sm">
                        <span class="icon icon-trash-alt text-danger" role="presentation" aria-hidden="true"></span>
                        <span class="visually-hidden">{str tag='delete'}</span>
                    </button>
                    {/if}
                </div>
            </td>
            {/if}
        </tr>
        {/foreach}
    </tbody>
</table>
</div>
{else}
<p class="no-results">
    <button class="btn btn-secondary" data-url="{$WWWROOT}artefact/internal/socialprofile.php">
        <span class="icon icon-plus left" role="presentation" aria-hidden="true"></span>
        {str tag=newsocialprofile section=artefact.internal}
    </button>
</p>
{/if}
{$pagination.html|safe}
