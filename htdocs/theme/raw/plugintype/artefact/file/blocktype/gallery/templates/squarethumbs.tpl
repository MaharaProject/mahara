<div id="thumbnails{$instanceid}" class="card-body thumbnails js-masonry">
    {foreach from=$images item=image}
        <div {if $image.squaredimensions}style="width:{$image.squaredimensions}px;height:{$image.squaredimensions}px;"{/if} class="thumb">
            <a {if $image.fancybox}class="gallery-popup" data-bs-target="#gallerymodal" data-toggle="modal" data-link="{$image.link}" data-caption="{$image.description}"{/if} title="{$image.title}">
                <img src="{$image.source}" alt="{$image.title}" title="{$image.title}" width="{$width}" height="{$width}"/>
            </a>
        {if $showdescription && $image.description}
        <p class="text-small title">
            {$image.description|truncate:60|clean_html|safe}
        </p>
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
            let modalimg = $('<img>', {literal}{{/literal}'src':$(btn).data('link'){literal}}{/literal});
            $('#gallerymodal').find('.modal-image').html(modalimg);
            $('#gallerymodal').find('.modal-caption').text($(btn).data('caption'));
            $('#gallerymodal').find('.modal-header-title').text($(btn).prop('title'));
        });
    });
{literal}}{/literal});
</script>