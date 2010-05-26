{if $searchable}<ul class="artefactchooser-tabs">
    <li{if !$.request.s} class="current"{/if}><a href="{$browseurl}">{str tag=Browse section=view}</a></li>
    <li{if $.request.s} class="current"{/if}><a href="{$searchurl}">{str tag=Search section=view}</a></li>
</ul>{/if}
<div id="artefactchooser-body">
    <div class="cb artefactchooser-splitter">
        <div id="artefactchooser-searchform"{if !$.request.s} class="hidden"{/if}> {* Use a smarty var, not smarty.request *}
            <label><input type="text" class="text" id="artefactchooser-searchfield" name="search" value="{$.request.search}" tabindex="42">
            <input type="hidden" name="s" value="1">
            <input type="submit" class="submit" id="artefactchooser-searchsubmit" name="action_acsearch_id_{$blockinstance}" value="&raquo;" tabindex="42"></label>
        </div>
        {if $artefacts}
        <table id="{$datatable}" class="artefactchooser-data">
            <tbody>
                {$artefacts|safe}
            </tbody>
        </table>
        {$pagination|safe}
        {else}
        <p class="noartefacts">{str tag=noartefactstochoosefrom section=view}</p>
        {/if}
    </div>
</div>
