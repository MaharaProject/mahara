{include file="search:elasticsearch:facets.tpl" facets=$facets}
<div class="subpage universalsearch card">
    <div id="resultswrap" class="{if $selected eq 'Text' || $selected eq 'Media' || $selected eq 'Portfolio'}filter{else}nofilter{/if}">
        {if $totalresults > 0}
            <div class="elasticsearch-filters clearfix">
                {if $selected neq ''}
                <div id="universalsearchresults-filter-wrap" class="filter clearfix">
                    {if $selected eq 'Text' || $selected eq 'Media' || $selected eq 'Portfolio'}
                        {include file="search:elasticsearch:search-filter-content.tpl"}
                    {/if}
                    <div class="filter-wrapper">
                        {include file="search:elasticsearch:search-sort.tpl"}
                        {if $selected neq 'User'}
                        {include file="search:elasticsearch:search-filter-owner.tpl"}
                        {/if}
                    </div>
                </div>
                <div class="filter clearfix">
                    <div class="filter-wrapper">
                    {if $selected eq 'Text' || $selected eq 'Media'}
                    {include file="search:elasticsearch:search-filter-licence.tpl"}
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
                        {include file="search:elasticsearch:user.tpl" user=$record['db']}
                    {elseif $record['type'] eq 'interaction_forum_post'}
                        {include file="search:elasticsearch:interaction_forum_post.tpl" record=$record['db']}
                    {elseif $record['type'] eq 'interaction_instance'}
                        {include file="search:elasticsearch:interaction_instance.tpl" record=$record['db']}
                    {elseif $record['type'] eq 'view'}
                        {include file="search:elasticsearch:view.tpl" record=$record['db']}
                    {elseif $record['type'] eq 'group'}
                        {include file="search:elasticsearch:group.tpl" record=$record['db']}
                    {elseif $record['type'] eq 'artefact'}
                        {include file="search:elasticsearch:artefact.tpl" record=$record['db'] secfacetterm=$record['secfacetterm']}
                    {elseif $record['type'] eq 'block_instance'}
                        {include file="search:elasticsearch:block_instance.tpl" record=$record['db']}
                    {elseif $record['type'] eq 'collection'}
                        {include file="search:elasticsearch:collection.tpl" record=$record['db']}
                    {/if}
                </div>
            {/foreach}
            </div>
        {else}
            <p class="no-results">{str tag=nosearchresultsfound section=group}</p>
        {/if}
    </div>
</div>