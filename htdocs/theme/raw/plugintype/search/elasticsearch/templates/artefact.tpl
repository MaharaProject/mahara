<div class="row">
    <div class="col-md-8">
        {if ($secfacetterm == "Image" || $record->artefacttype == 'profileicon') && $record->thumb}
            <img src="{$record->thumb}" alt="" class="artefact-img">
            <h3 class="title list-group-item-heading text-inline">
                {if $record->link}
                    <a href="{$WWWROOT}{$record->link}">
                        {$record->title|str_shorten_html:50:true|safe}
                    </a>
                {else}
                    {$record->title|str_shorten_html:50:true|safe}
                {/if}
            </h3>
        {elseif $record->artefacttype == 'socialprofile'}
            <img src="{$record->icon}" alt="{$record->note}">
            <h3 class="title list-group-item-heading text-inline">
                {if $record->link}
                    <a href="{$record->link}">
                        {$record->title|str_shorten_html:50:true|safe}
                    </a>
                {else}
                    {$record->title|str_shorten_html:50:true|safe}
                {/if}
            </h3>
        {else}
            <h3 class="title list-group-item-heading text-inline">
                <span class="icon icon-{$record->artefacttype} left {if $record->deleted}text-midtone{/if}" role="presentation" aria-hidden="true"></span>
                {if $record->link}
                    <a href="{$WWWROOT}{$record->link}">
                        {$record->title|str_shorten_html:50:true|safe}
                    </a>
                {else}
                    {$record->title|str_shorten_html:50:true|safe}
                {/if}
            </h3>
        {/if}
        <span class="artefacttype text-midtone">
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
        </span>
        {if $record->createdbyname}
        <div class="createdby">
            {str tag=createdby section=search.elasticsearch arg1='<a href="`$record->createdby|profile_url`">`$record->createdbyname`</a>'}
        </div>
        {/if}
        <div class="detail">
            {if $record->highlight}
                {$record->highlight|safe}
            {else}
                {$record->description|str_shorten_html:140:true|safe}
            {/if}
        </div>
        <!-- TAGS -->
        {if is_array($record->tags) && count($record->tags) > 0}
        <div class="tags"><strong>{str tag=tags section=search.elasticsearch}:</strong>
            {foreach from=$record->tags item=tag name=tags}
                <a href="{$WWWROOT}search/elasticsearch/index.php?query={$tag}&tagsonly=true">{$tag}</a>{if !$.foreach.tags.last}, {/if}
            {/foreach}
        </div>
        {/if}
    </div>
    <!-- RESUMEITEMS -->
    <div class="col-md-4">
        {if is_array($record->resumeitems) && count($record->resumeitems) > 0}
        <strong>{str tag=contains section=search.elasticsearch}:</strong>
        <ul>
        {foreach from=$record->resumeitems key=rid item=resume}
            {if $resume->title}<li>{$resume->title}</li>{/if}
            {if $resume->jobtitle}<li>{$resume->jobtitle}</li>{/if}
            {if $resume->qualname}<li>{$resume->qualname}</li>{/if}
        {/foreach}
        </ul>
        {/if}

        <!-- VIEWS -->
        {if is_array($record->views) && count($record->views) > 0}
        <div class="usedon">
            {if count($record->views) > 1}
                <strong>{str tag=usedonpages section=search.elasticsearch}:</strong>
            {else}
                <strong>{str tag=usedonpage section=search.elasticsearch}:</strong>
            {/if}
            <ul class="list-group list-unstyled">
            {foreach from=$record->views key=id item=view}
                <li>
                    <a href="{$WWWROOT}view/view.php?id={$id}">{$view|str_shorten_html:50:true|safe}</a>
                    <!-- Profile artefact can only be displayed in views -->
                    {if $secfacetterm != "Profile"} |
                    <span class="viewartefact">
                        <a href="{$WWWROOT}artefact/artefact.php?artefact={$record->id}&view={$id}">
                        {str tag=viewartefact}
                        {if $secfacetterm == "Journalentry"}
                            {str tag=blogpost section=search.elasticsearch}
                        {elseif $secfacetterm == "Forumpost"}
                            {str tag=forumpost section=search.elasticsearch}
                        {elseif $secfacetterm == "Resume"}
                            {str tag=resume section=search.elasticsearch}
                        {elseif $secfacetterm == "Wallpost"}
                            {str tag=wallpost section=search.elasticsearch}
                        {elseif $result->artefacttype == "blog"}
                            {str tag=blog section=search.elasticsearch}
                        {elseif $result->artefacttype == "html"}
                            {str tag=html section=search.elasticsearch}
                        {else}
                            {$secfacetterm|lower}
                        {/if}
                        </a>
                    </span>
                {/if}
                </li>
            {/foreach}
            </ul>
        </div>
        {/if}
    </div>
</div>
