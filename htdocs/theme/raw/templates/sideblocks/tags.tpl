<div class="card">
    <h3 class="card-header has-link">
        <a href="{$WWWROOT}tags.php">{str tag="tags"} <span class="icon icon-arrow-right float-right" role="presentation" aria-hidden="true"></span></a>
    </h3>
    <div class="tagblock card-body">
        {if $sbdata.tags}
            {foreach from=$sbdata.tags item=tag}
                <a class="tag"{if $tag->size} style="font-size: {$tag->size}em;"{/if} href="{$WWWROOT}tags.php?tag={$tag->tagurl|safe}" title="{str tag=nitems arg1=$tag->count}">{$tag->tag|str_shorten_text:30}</a> &nbsp;
            {/foreach}
        {else}
            <div class="no-results-small text-small">{str tag=youhavenottaggedanythingyet}</div>
        {/if}
    </div>
</div>
