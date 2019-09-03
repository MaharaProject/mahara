<div class="imageblock card-body" itemscope itemtype="http://schema.org/ImageObject">
    <div class="image">
        {if !$editing}
        <a class="modal_link" data-toggle="modal-docked" data-target="#configureblock" href="#" data-artefactid="{$artefactid}"  data-target="#configureblock" >
            <img src="{$src}" alt="{$description}" itemprop="contentURL" data-blockid="{$blockid}">
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
