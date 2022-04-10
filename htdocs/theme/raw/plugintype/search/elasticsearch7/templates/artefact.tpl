{if ($secfacetterm == "Image" || $record->artefacttype == 'profileicon') && $record->thumb}
    <h2 class="list-group-item-heading text-inline">
        <img src="{$record->thumb}" alt="" class="artefact-img">
        {if $record->link}
            <a href="{$WWWROOT}{$record->link}">
                {$record->title|str_shorten_html:50:true|safe}
            </a>
        {else}
            {$record->title|str_shorten_html:50:true|safe}
        {/if}
{elseif $record->artefacttype == 'socialprofile'}
    <h2 class="title list-group-item-heading text-inline">
        {if $record->icon}
        <img src="{$record->icon}" alt="{$record->note}" class="artefact-img">
        {elseif $record->icon_class}
        <span class="{$record->icon_class} left {if $record->deleted}text-midtone{/if}" role="presentation" aria-hidden="true"></span>
        {/if}
        {if $record->link}
            <a href="{$record->link}">
                {$record->title|str_shorten_html:50:true|safe}
            </a>
        {else}
            {$record->title|str_shorten_html:50:true|safe}
        {/if}
{else}
    <h2 class="title list-group-item-heading text-inline">
        <span class="icon icon-{$record->artefacttype} left {if $record->deleted}text-midtone{/if}" role="presentation" aria-hidden="true"></span>
        {if $record->link}
            <a href="{$WWWROOT}{$record->link}">
                {$record->title|str_shorten_html:50:true|safe}
            </a>
        {else}
            {$record->title|str_shorten_html:50:true|safe}
        {/if}
{/if}
        <span class="artefacttype text-midtone text-regular">
            {if $secfacetterm == "Journalentry"}
                ({str tag=blogpost section=search.elasticsearch7})
            {elseif $secfacetterm == "Forumpost"}
                ({str tag=forumpost section=search.elasticsearch7})
            {elseif $secfacetterm == "Resume"}
                ({str tag=resume section=search.elasticsearch7})
            {elseif $secfacetterm == "Wallpost"}
                ({str tag=wallpost section=search.elasticsearch7})
            {else}
                ({$secfacetterm})
            {/if}
            {if $record->deleted}
                ({str tag=deleted section=search.elasticsearch7})
            {/if}
        </span>
    </h2>
<div class="row">
    <div class="col-md-7">
        {if $record->createdbyname}
        <div class="createdby">
            {str tag=createdby section=search.elasticsearch7 arg1='<a href="`$record->createdby|profile_url`">`$record->createdbyname`</a>'}
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
        <div class="tags text-small">{str tag=tags section=search.elasticsearch7}:
            {foreach from=$record->tags item=tag name=tags}
                <a href="{$WWWROOT}{$thispath}?query={$tag}&tagsonly=true">{$tag}</a>{if !$.foreach.tags.last}, {/if}
            {/foreach}
        </div>
        {/if}
    </div>
    <!-- RESUMEITEMS -->
    <div class="col-md-5">
        {if is_array($record->resumeitems) && count($record->resumeitems) > 0}
        <span>{str tag=contains section=search.elasticsearch7}:</span>
        <ul class="list-group list-unstyled">
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
                {str tag=usedonpages section=search.elasticsearch7}:
            {else}
                {str tag=usedonpage section=search.elasticsearch7}:
            {/if}
            <ul class="list-group list-unstyled">
            {foreach from=$record->views key=id item=view}
                <li>
                    <a href="{$WWWROOT}view/view.php?id={$id}">{$view|str_shorten_html:50:true|safe}</a>
                    <!-- Profile artefact can only be displayed in views -->
                    {if $secfacetterm != "Profile"}
                    <span class="viewartefact">[
                        <a href="{$WWWROOT}view/view.php?id={$id}&modal=1&artefact={$record->id}">
                        {str tag=viewartefact}
                        {if $secfacetterm == "Journalentry"}
                            {str tag=blogpost section=search.elasticsearch7}
                        {elseif $secfacetterm == "Forumpost"}
                            {str tag=forumpost section=search.elasticsearch7}
                        {elseif $secfacetterm == "Resume"}
                            {str tag=resume section=search.elasticsearch7}
                        {elseif $secfacetterm == "Wallpost"}
                            {str tag=wallpost section=search.elasticsearch7}
                        {elseif $result->artefacttype == "blog"}
                            {str tag=blog section=search.elasticsearch7}
                        {elseif $result->artefacttype == "html"}
                            {str tag=html section=search.elasticsearch7}
                        {else}
                            {$secfacetterm|lower}
                        {/if}
                        </a>]
                    </span>
                {/if}
                </li>
            {/foreach}
            </ul>
        </div>
        {/if}
    </div>
</div>
