{include file="search:elasticsearch:facets.tpl" facets=$facets}
<div class="subpage universalsearch">
    {if $selected neq ''}
    <div class="selectboxes">
        {if $selected neq 'User'}
            {include file="search:elasticsearch:search-filter-owner.tpl"}
        {/if}
        {include file="search:elasticsearch:search-sort.tpl"}
    </div>
    {/if}
    <div id="resultswrap" class="{if $selected eq 'Text' || $selected eq 'Media' || $selected eq 'Portfolio'}filter{else}nofilter{/if}">
        <div id="universalsearchresults-filter-wrap">
        {if $selected neq ''}
            {if $selected eq 'Text' || $selected eq 'Media' || $selected eq 'Portfolio'}
                {include file="search:elasticsearch:search-filter-content.tpl"}
                {if $selected eq 'Text' || $selected eq 'Media'}
                    {include file="search:elasticsearch:search-filter-licence.tpl"}
                {/if}
            {/if}
        {/if}
        </div>
        <div id="universalsearchresults" class="listing fullwidth">
        {if $data}
        {counter start=$offset print=false}
        {foreach from=$data item=record name=foo}
            <div class="{cycle values='r0,r1'} listrow">
                <div class="listrowright">
                    <div class="counter">{counter print=true}.</div>
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
                    {elseif $record['type'] eq 'collection'}
                        {include file="search:elasticsearch:collection.tpl" record=$record['db']}
                    {/if}
                 </div>
            </div>
        {/foreach}
        {elseif $query}
            <div class="emptyresults">
                <div class="message">{str tag=nosearchresultsfound section=group}</div>
            </div>
        {/if}
        </div>
    </div>
    <div class="cl"></div>
</div>