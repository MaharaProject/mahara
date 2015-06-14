{if $searchable}
<ul class="artefactchooser-tabs nav nav-tabs">
    <li{if !$.request.s} class="current active"{/if}><a href="{$browseurl}">{str tag=Browse section=view}</a></li>
    <li{if $.request.s} class="current active"{/if}><a href="{$searchurl}">{str tag=Search section=view}</a></li>
</ul>{/if}
<div id="artefactchooser-body">
    <div class="cb artefactchooser-splitter">
        <div id="artefactchooser-searchform" class="form-group" {if !$.request.s} class="hidden"{/if}> {* Use a smarty var, not smarty.request *}
            <label for="artefactchooser-searchfield">{str tag=search section=mahara}</label>
            <input type="text" class="text form-control" id="artefactchooser-searchfield" name="search" value="{$.request.search}" tabindex="42">
            <input type="hidden" name="s" value="1">
            <input class="submit btn btn-success" type="submit" id="artefactchooser-searchsubmit" name="action_acsearch_id_{$blockinstance}" value="{str tag=go}" tabindex="42">
        </div>
        {if !$artefacts}
        <p class="noartefacts">{str tag=noartefactstochoosefrom section=view}</p>
        {/if}
        <div id="{$datatable}" class="artefactchooser-data form-group checkbox last">
            {$artefacts|safe}
        </div>
        {$pagination|safe}
    </div>
</div>