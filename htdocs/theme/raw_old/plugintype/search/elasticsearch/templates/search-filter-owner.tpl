{*
    Links must contain the following parameters:
    - query
    - mainfacetterm
    - secfacetterm
    - owner
    - portofolio
    - sort
    - license
*}
<div id="universalsearch-filter-owner" class="search-filter-owner pieform with-label-widthauto form-condensed">
    <input type="hidden" id="search-filter-owner-url" value="{$WWWROOT}search/elasticsearch/index.php?query={$query}&mainfacetterm={$selected}&secfacetterm={$contentfilterselected}&license={$license}&sort={$sort}{if $tagsonly}&tagsonly=true{/if}&limit={$limit}" />
    <div class="select form-group">
        <label for="search-filter-owner">
            {if $selected eq 'Text' || $selected eq 'Media' || $selected eq 'Portfolio'}
                {str tag=owner section=search.elasticsearch}:
            {else}
                {str tag=admin section=search.elasticsearch}:
            {/if}
        </label>
        <span class="picker">
            <select id="search-filter-owner" class="form-control select autofocus">
                {foreach from=$ownerfilter item=item}
                    <option value="{$item.term}" {if ($owner == $item.term)}selected{/if}>{$item.display}</option>
                {/foreach}
            </select>
        </span>
    </div>
</div>