<div class="imageblock card-body" itemscope itemtype="http://schema.org/ImageObject">
    <div class="image">
        <a href="{$url}">
            <img src="{$src}" alt="{$description}" itemprop="contentURL">
        </a>
    </div>

    {if $showdescription}
    <div class="detail" itemprop="description">
        {$description|safe|clean_html}
    </div>
    {/if}
</div>

{$comments|safe}
