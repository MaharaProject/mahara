{*
    Links must contain the following parameters:
    - query
    - mainfacetterm
    - secfacetterm
    - owner
    - sort
    - license
*}
{if isset($license_on)}
<div id="results_filter" class="search-filter-license pieform with-label-widthauto form-condensed">
    <input type="hidden" id="search-filter-license-url" value="{$WWWROOT}search/elasticsearch/index.php?query={$query}&mainfacetterm={$selected}&secfacetterm={$contentfilterselected}&sort={$sort}&owner={$owner}{if $tagsonly}&tagsonly=true{/if}&limit={$limit}" />
    <div class="form-group select">
        <label for="search-filter-license" class="filtertitle">
            {str tag=license section=search.elasticsearch}:
        </label>
        <span class="picker">
        {if isset($license_options)}
        <select id="search-filter-license" class="form-control select">
            <option value="all">{str tag=all section=search.elasticsearch}</option>
            {foreach from=$license_options key=id item=item}
                <option value="{$id}" {if ($license == $id)}selected{/if}>{$item}</option>
            {/foreach}
        </select>
        {else}
        <select disabled class="form-control select"><option>{str tag=all section=search.elasticsearch}</option></select>
        {/if}
        </span>
    </div>
</div>
{/if}