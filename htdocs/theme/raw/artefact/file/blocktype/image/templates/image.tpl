<div class="imageblock panel-body" itemscope itemtype="http://schema.org/ImageObject">
    <div class="image">
        <a href="{$url}">
            <img src="{$src}" alt="{$description}" itemprop="contentURL">
        </a>
    </div>

    {if $showdescription}
    <div class="detail" itemprop="description">
        {$description}
    </div>
    {/if}
</div>

{$comments|safe}
