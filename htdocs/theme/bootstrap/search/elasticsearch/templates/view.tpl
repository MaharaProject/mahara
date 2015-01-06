{if $record->deleted}
    <h3 class="title">{$record->title} <span class="artefacttype">({str tag=deleted section=search.elasticsearch})</span></h3>
{else}
    <h3 class="title"><a href="{$WWWROOT}view/view.php?id={$record->id}">{$record->title}</a> <span class="artefacttype">({str tag=page section=search.elasticsearch})</span></h3>
    {if $record->createdbyname}
      <div class="createdby">
        {if $record->anonymise}
            {str tag=createdbyanon section=search.elasticsearch}
        {else}
            {str tag=createdby section=search.elasticsearch arg1='<a href="`$record->createdby|profile_url`">`$record->createdbyname|safe`</a>'}
        {/if}
      </div>
    {/if}
      <div class="detail">{$record->description|str_shorten_html:140:true|safe}</div>
    <!-- TAGS -->
    {if $record->tags|count gt 0}
    <div class="tags"><strong>{str tag=tags section=search.elasticsearch}:</strong>
    {foreach from=$record->tags item=tag name=tags}
        <a href="{$WWWROOT}search/elasticsearch/index.php?query={$tag}&tagsonly=true">{$tag}</a>{if !$.foreach.tags.last}, {/if}
    {/foreach}
    </div>
    {/if}
    <!-- end TAGS -->
{/if}