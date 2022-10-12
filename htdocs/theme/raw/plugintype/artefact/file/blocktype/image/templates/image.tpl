<div class="imageblock card-body" itemscope itemtype="http://schema.org/ImageObject">
    <div class="image">
        {if !$editing}
            <a class="modal_link" data-bs-toggle="modal-docked" data-bs-target="#configureblock" href="#" data-artefactid="{$artefactid}"  data-blockid="{$blockid}">
            {if $showdescription && !$isdecorative}
                <div class="detail" itemprop="description">
                <figure class="figure">
                    <img class="figure-img" src="{$src}" alt="{if !$altiscaption}{$alttext}{/if}" itemprop="contentURL" data-bs-target="#configureblock" data-artefactid="{$artefactid}" data-blockid="{$blockid}">
                    <figcaption class="figure-caption">
                    {if $altiscaption}
                        {$alttext|clean_html|safe}
                    {else}
                        {$description|clean_html|safe}
                    {/if}
                    </figcaption>
                </figure>
                </div>
            {else}
                <img src="{$src}" alt="{if !$isdecorative}{$alttext}{/if}" itemprop="contentURL" data-bs-target="#configureblock" data-artefactid="{$artefactid}" data-blockid="{$blockid}">
            {/if}
            </a>
        {else}
            {if $showdescription && !$isdecorative}
                <div class="detail" itemprop="description">
                <figure class="figure">
                    <img class="figure-img" src="{$src}" alt="{if !$altiscaption}{$alttext}{/if}" itemprop="contentURL">
                    <figcaption class="figure-caption">
                    {if $altiscaption}
                        {$alttext|clean_html|safe}
                    {else}
                        {$description|clean_html|safe}
                    {/if}
                    </figcaption>
                </figure>
                </div>
            {else}
                <img src="{$src}" alt="{if !$isdecorative}{$alttext}{/if}" itemprop="contentURL">
            {/if}
        {/if}
    </div>
</div>