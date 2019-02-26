{if $searchable}
<ul class="artefactchooser-tabs nav nav-tabs">
    <li><a {if !$.request.s} class="current active"{/if} href="{$browseurl}">{str tag=Browse section=view}</a></li>
    <li><a {if $.request.s} class="current active"{/if} href="{$searchurl}">{str tag=Search section=view}</a></li>
</ul>
{/if}
<div id="artefactchooser-body">
    <div class="artefactchooser-splitter">
        <div id="artefactchooser-searchform" class="artefactchooser-search input-group clearfix {if !$.request.s} d-none{/if}"> {* Use a smarty var, not smarty.request *}
            <label class="sr-only" for="artefactchooser-searchfield">
                {str tag=search section=mahara}
            </label>
            <input type="text" class="text form-control" id="artefactchooser-searchfield" name="search" value="{$.request.search}" tabindex="42">
            <input type="hidden" name="s" value="1">
            <span class="input-group-append">
                <button class="submit btn btn-primary" type="submit" id="artefactchooser-searchsubmit" name="action_acsearch_id_{$blockinstance}" tabindex="42">
                    {str tag=search}
                </button>
            </span>
        </div>

        <div id="{$datatable}" class="artefactchooser-data list-group list-group-lite">
            {if !$artefacts}
            <span class="noartefacts lead">
                {str tag=noartefactstochoosefrom section=view}
            </span>
            {else}
            {$artefacts|safe}
            {/if}
        </div>

        {$pagination|safe}
    </div>
</div>
