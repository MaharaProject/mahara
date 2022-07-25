<div class="card-body">
    {if $images}
    <div id="slideshow{$instanceid}" class="carousel slide" data-ride="carousel" data-interval="false">
        <div class="carousel-inner" role="listbox">
            {foreach from=$images item=image key=k name=images}

            <div class="{if $dwoo.foreach.images.first}carousel-item active{else}carousel-item{/if}">

                {if $showdescription && !$image.isdecorative}
                    <img src="{$image.source}" alt="{if !$image.altiscaption}{$image.alttext}{/if}" class="mx-auto d-block" role="slide">
                    <div class="carousel-caption" id="description_{$instanceid}_{$k}">
                    {if $image.altiscaption}
                        {$image.alttext|clean_html|safe}
                    {else}
                        {$image.description|clean_html|safe}
                    {/if}
                    </div>
                {else}
                    <img src="{$image.source}" alt="{if !$image.isdecorative}{$image.alttext}{/if}" class="mx-auto d-block" role="slide">
                {/if}
            </div>

            {/foreach}
        </div>

        <a class="carousel-control-prev carousel-control" href="#slideshow{$instanceid}" role="button" data-bs-slide="prev" title="{str tag=previous}">
            <span class="icon icon-angle-left icon-lg" role="presentation" aria-hidden="true"></span>
            <span class="visually-hidden">{str tag=previous}</span>
        </a>
        <a class="carousel-control-next carousel-control" href="#slideshow{$instanceid}" role="button" data-bs-slide="next" title="{str tag=next}">
            <span class="icon icon-angle-right icon-lg" role="presentation" aria-hidden="true"></span>
            <span class="visually-hidden">{str tag=next}</span>
        </a>
    </div>
    {else}
      {str tag=noimagesfound section=artefact.file}
    {/if}
</div>

{$comments|safe}
