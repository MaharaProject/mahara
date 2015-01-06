
{if $selected == "Media"}
<div class="thumbnail-right">
    {if $secfacetterm == "Image" && $record->thumb}
        <div class="thumbnail-image"><img src="{$record->thumb}" alt=""></div>
    {else}
        <div class="thumbnail-image"><img src="{$WWWROOT}search/elasticsearch/theme/raw/static/images/thumbnail-{$secfacetterm|lower}.png" alt=""></div>
    {/if}
{/if}
{if $record->link}
    <h3 class="title">
        {if $record->artefacttype == 'socialprofile'}
            <img src="{$record->icon}" alt="{$record->note}">
            {$record->note|str_shorten_html:50:true|safe}
            <a href="{$record->link}" title="{$record->link}" target="_blank" class="socialprofile">{$record->title|str_shorten_html:50:true|safe}</a>
        {else}
            <a href="{$WWWROOT}{$record->link}">{$record->title|str_shorten_html:50:true|safe}</a>
        {/if}
{else}
    <h3 class="title">{$record->title|str_shorten_html:50:true|safe} 
{/if}
<span class="artefacttype">
    {if $secfacetterm == "Journalentry"}
        ({str tag=blogpost section=search.elasticsearch})
    {elseif $secfacetterm == "Forumpost"}
        ({str tag=forumpost section=search.elasticsearch})
    {elseif $secfacetterm == "Resume"}
        ({str tag=resume section=search.elasticsearch})
    {elseif $secfacetterm == "Wallpost"}
        ({str tag=wallpost section=search.elasticsearch})
    {else}
        ({$secfacetterm})
    {/if}
    {if $record->deleted}
        ({str tag=deleted section=search.elasticsearch})
    {/if}
</span></h3>
{if $record->createdbyname}
    <div class="createdby">{str tag=createdby section=search.elasticsearch arg1='<a href="`$record->createdby|profile_url`">`$record->createdbyname|safe`</a>'}</div>
{/if}
<div class="detail">{$record->description|str_shorten_html:100:true|safe}</div>
<!-- VIEWS -->
{if $record->views|count gt 0}
    <div class="usedon">
        {if $record->views|count gt 1}
            <strong>{str tag=usedonpages section=search.elasticsearch}:</strong>
            <ul>
            {foreach from=$record->views key=id item=view}
                <li><a href="{$WWWROOT}view/view.php?id={$id}">{$view|str_shorten_html:50:true|safe}</a>
                <!-- Profile artefact can only be displayed in views -->
                {if $secfacetterm != "Profile"}
                     |
                    <span class="viewartefact">
                    <a href="{$WWWROOT}artefact/artefact.php?artefact={$record->id}&view={$id}">view
                    {if $secfacetterm == "Journalentry"}
                      {str tag=blogpost section=search.elasticsearch}
                    {elseif $secfacetterm == "Forumpost"}
                      {str tag=forumpost section=search.elasticsearch}
                    {elseif $secfacetterm == "Resume"}
                      {str tag=resume section=search.elasticsearch}
                    {elseif $secfacetterm == "Wallpost"}
                      {str tag=wallpost section=search.elasticsearch}
                    {else}
                      {$secfacetterm|lower}
                    {/if}
                    </a>
                    </span>
                {/if}
                </li>
            {/foreach}
            </ul>
          {else}
            <strong>{str tag=usedonpage section=search.elasticsearch}:</strong>
            <ul>
            {foreach from=$record->views key=id item=view}
                  <li><a href="{$WWWROOT}view/view.php?id={$id}">{$view|str_shorten_html:50:true|safe}</a>
                <!-- Profile artefact can only be displayed in views -->
                {if $secfacetterm != "Profile"}
                     |
                    <span class="viewartefact">
                    <a href="{$WWWROOT}artefact/artefact.php?artefact={$record->id}&view={$id}">view
                    {if $secfacetterm == "Journalentry"}
                      {str tag=blogpost section=search.elasticsearch}
                    {elseif $secfacetterm == "Forumpost"}
                      {str tag=forumpost section=search.elasticsearch}
                    {elseif $secfacetterm == "Resume"}
                      {str tag=resume section=search.elasticsearch}
                    {elseif $secfacetterm == "Wallpost"}
                      {str tag=wallpost section=search.elasticsearch}
                    {else}
                    {$secfacetterm|lower}
                    {/if}
                    </a>
                    </span>
                {/if}
                </li>
            {/foreach}
            </ul>
        {/if}
    </div>
{/if}
<!-- end VIEWS -->
<!-- TAGS -->
{if $record->tags|count gt 0}
<div class="tags"><strong>{str tag=tags section=search.elasticsearch}:</strong>
    {foreach from=$record->tags item=tag name=tags}
        <a href="{$WWWROOT}search/elasticsearch/index.php?query={$tag}&tagsonly=true">{$tag}</a>{if !$.foreach.tags.last}, {/if}
    {/foreach}
</div>
{/if}
{if $selected == "Media"}
</div>
{/if}
<!-- end TAGS -->
