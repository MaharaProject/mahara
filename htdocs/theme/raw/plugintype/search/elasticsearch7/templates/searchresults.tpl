{include file="search:elasticsearch7:facets.tpl" facets=$facets}
<div class="subpage universalsearch card">
    <div id="resultswrap" class="{if $selected eq 'Text' || $selected eq 'Media' || $selected eq 'Portfolio'}filter{else}nofilter{/if}">
        {if $totalresults > 0}
            <div class="elasticsearch-filters clearfix">
                {if $selected neq ''}
                <div id="universalsearchresults-filter-wrap" class="filter clearfix">
                    {if $selected eq 'Text' || $selected eq 'Media' || $selected eq 'Portfolio'}
                        {include file="search:elasticsearch7:search-filter-content.tpl"}
                    {/if}
                    <div class="filter-wrapper">
                        {include file="search:elasticsearch7:search-sort.tpl"}
                        {if $selected neq 'User'}
                        {include file="search:elasticsearch7:search-filter-owner.tpl"}
                        {/if}
                    </div>
                </div>
                <div class="filter clearfix">
                    <div class="filter-wrapper">
                        {if $selected eq 'Text' || $selected eq 'Media'}
                        {include file="search:elasticsearch7:search-filter-licence.tpl"}
                        {/if}
                    </div>
                </div>
                {/if}
            </div>
        {/if}
        {if $data}
            <div id="universalsearchresults" class="list-group list-group-lite mb0">
            {counter start=$offset print=false}
            {foreach from=$data item=record name=foo}
                <div class="list-group-item">
                    {if $record['type'] eq 'usr'}
                        {include file="search:elasticsearch7:user.tpl" user=$record['db']}
                    {elseif $record['type'] eq 'interaction_forum_post'}
                        {include file="search:elasticsearch7:interaction_forum_post.tpl" record=$record['db']}
                    {elseif $record['type'] eq 'interaction_instance'}
                        {include file="search:elasticsearch7:interaction_instance.tpl" record=$record['db']}
                    {elseif $record['type'] eq 'view'}
                        {include file="search:elasticsearch7:view.tpl" record=$record['db']}
                    {elseif $record['type'] eq 'group'}
                        {include file="search:elasticsearch7:group.tpl" record=$record['db']}
                    {elseif $record['type'] eq 'artefact'}
                        {include file="search:elasticsearch7:artefact.tpl" record=$record['db'] secfacetterm=$record['secfacetterm']}
                    {elseif $record['type'] eq 'block_instance'}
                        {include file="search:elasticsearch7:block_instance.tpl" record=$record['db']}
                    {elseif $record['type'] eq 'collection'}
                        {include file="search:elasticsearch7:collection.tpl" record=$record['db']}
                    {else}
                        <p>{$record['type']} not handled.</p>
                    {/if}
                </div>
            {/foreach}
            </div>
        {else}
            <p class="no-results">{str tag=nosearchresultsfound section=group}</p>
        {/if}
    </div>
</div>