<p id="{$prefix}_empty_selectlist" class="{if $selectedlist}hidden{/if} lead">
    {if !$selectfolders}
    {str tag=nofilesfound section=artefact.file}
    {/if}
</p>
<table id="{$prefix}_selectlist"  class="fullwidth{if !$selectedlist} hidden{/if} table table-selectedlist">
    <tbody>
        {foreach from=$selectedlist item=file}
        {assign var=displaytitle value=$file->title|str_shorten_text:34|safe}
        <tr class="active{if $highlight && $highlight == $file->id} highlight-file{/if}">
            <td class="icon-container">
                {if $file->artefacttype !== 'image'}
                    <span class="icon icon-{$file->artefacttype} icon-lg" role="presentation" aria-hidden="true"></span>
                {else}
                    <img src="{if $file->artefacttype == 'image' || $file->artefacttype == 'profileicon'}{$WWWROOT}artefact/file/download.php?file={$file->id}&size=24x24{else}{theme_url filename=images/`$file->artefacttype`.png}{/if}">
                {/if}
            </td>
            <td class="filename">
                {if $selectfolders}
                    <span class="js-display-title">{$displaytitle}</span>
                {else}
                    <a href="{$WWWROOT}artefact/file/download.php?file={$file->id}" title="{str tag=downloadfile section=artefact.file arg1=$displaytitle}" class="js-display-title">
                        {$displaytitle}
                    </a>
                {/if}
            </td>
            <td class="text-right s">
                <button id="{$prefix}_unselect_{$file->id}" name="{$prefix}_unselect[{$file->id}]" class="btn btn-default btn-xs text-small button submit unselect" title="{str tag=remove}">
                    <span class="icon icon-times icon-lg text-danger left" role="presentation" aria-hidden="true"></span>
                    <span>{str tag=remove}</span>
                </button>
                 <input type="hidden" class="hidden" id="{$prefix}_selected[{$file->id}]" name="{$prefix}_selected[{$file->id}]" value="{$file->id}">
            </td>
        </tr>
        {/foreach}
     </tbody>
</table>
