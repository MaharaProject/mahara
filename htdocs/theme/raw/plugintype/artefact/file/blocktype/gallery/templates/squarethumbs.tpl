<div id="thumbnails{$instanceid}" class="card-body thumbnails js-masonry">
    {foreach from=$images item=image}
        <div {if $image.squaredimensions}style="width:{$image.squaredimensions}px;"{/if} class="thumb">
            {if $showdescription && ($image.description || $image.altiscaption) && !$image.isdecorative}
                <a {if $image.fancybox}class="gallery-popup" data-bs-target="#gallerymodal" data-bs-toggle="modal" data-bs-link="{$image.link}" data-bs-caption="{$image.bootstrapcaption}" data-bs-title="{$image.title}"{/if} aria-describedby="{$image.id}">
                    <figure class="figure">
                    <img class="figure-img" style="height:{$image.squaredimensions}px;" src="{$image.source}" id="{$image.id}" alt="{if !$image.altiscaption && !$image.isdecorative}{$image.alttext}{/if}" width="{$width}" height="{$width}"/>
                        <figcaption class="figure-caption">
                        {if $image.altiscaption}
                            {$image.alttext|truncate:60|clean_html|safe}
                        {else}
                            {$image.description|truncate:60|clean_html|safe}
                        {/if}
                        </figcaption>
                     </figure>
                </a>
            {else}
                <a {if $image.fancybox}class="gallery-popup" data-bs-target="#gallerymodal" data-bs-toggle="modal" data-bs-link="{$image.link}" data-bs-caption="" data-bs-title="{$image.title}"{/if} aria-describedby="{$image.id}">
                    <img  style="height:{$image.squaredimensions}px;" src="{$image.source}" id="{$image.id}" alt="{if !$image.isdecorative}{$image.alttext}{/if}" width="{$width}" height="{$width}"/>
                </a>
            {/if}
        </div>
    {/foreach}
</div>
{if isset($copyright)}
<div id="lbBottom">
    {$copyright|safe}
</div>
{/if}

<!-- Modal -->
<div class="modal fade" id="gallerymodal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered gallery">
        <div class="modal-content">
            <div class="modal-header gallery">
                <div class="modal-header-title"></div>
                <div class="modal-title gallery">
                    <button class="deletebutton btn-close gallery" data-bs-dismiss="modal" aria-label="{str tag=Close}">
                        <span class="times">×</span>
                        <span class="visually-hidden">{str tag=Close}</span>
                    </button>
                </div>
            </div>
            <div class="modal-body gallery">
                <div class="modal-image"></div>
                <div class="modal-caption"></div>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(function($) {literal}{{/literal}
    $('.gallery-popup').each(function() {
        $(this).off('click');
        $(this).on('click', function(e) {
            e.preventDefault();
            let btn = e.target.closest('a');
            let modalimg = $('<img>', {literal}{{/literal}'src':$(btn).data('bs-link'),'alt':$(btn).find('img').prop('alt'){literal}}{/literal});
            $('#gallerymodal').find('.modal-image').html(modalimg);
            $('#gallerymodal').find('.modal-caption').text($(btn).data('bs-caption'));
            $('#gallerymodal').find('.modal-header-title').text($(btn).data('bs-title'));
        });
    });
{literal}}{/literal});
</script>