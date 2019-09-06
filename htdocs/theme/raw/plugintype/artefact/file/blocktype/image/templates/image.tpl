<div class="imageblock card-body" itemscope itemtype="http://schema.org/ImageObject">
    <div class="image">
        {if !$editing}
        <a class="modal_link" data-toggle="modal-docked" data-target="#configureblock" href="#" data-artefactid="{$artefactid}"  data-blockid="{$blockid}">
            <img src="{$src}" alt="{$description}" itemprop="contentURL" data-target="#configureblock" data-artefactid="{$artefactid}" data-blockid="{$blockid}" title="{$description}">
        </a>
        {else}
        <img src="{$src}" alt="{$description}" itemprop="contentURL">
        {/if}
    </div>

    {if $showdescription}
    <div class="detail" itemprop="description">
        {$description|safe|clean_html}
    </div>
    {/if}
</div>
