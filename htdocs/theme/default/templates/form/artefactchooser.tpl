{if $artefacts}
<table id="{$datatable}">
    <tbody>
{foreach from=$artefacts item=artefact}
        <tr title="{$artefact->hovertitle|escape}">
            <td style="width: 20px;">{if $selectone}<input type="radio" class="radio"id="{$elementname}_{$artefact->id}" name="{$elementname}" value="{$artefact->id|escape}"{if $value == $artefact->id} checked="checked"{/if}>{else}<input type="checkbox">{/if}</td>
            <td style="width: 22px;"><img src="{$artefact->icon|escape}" alt="*"></td>
            <th><label for="{$elementname}_{$artefact->id}">{if $artefact->description}{$artefact->description|escape}{else}{$artefact->title|escape}{/if}</label></th>
        </tr>
{/foreach}
    </tbody>
</table>
<div class="ac-pagination">{$pagination}{* mahara_pagelinks url=$baseurl count=$count limit=$limit offset=$offset offsetname=$offsetname datatable=$datatable json_script=view/artefactchooser.json.php firsttext='' previoustext='' nexttext='' lasttext='' numbersincludefirstlast=false *}</div>
<div class="ac-results">{$count} results</div>
{else}
<p>Sorry, no artefacts to choose from</p>
{/if}
