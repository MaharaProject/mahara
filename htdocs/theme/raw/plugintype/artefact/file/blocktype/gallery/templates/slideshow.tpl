<div class="panel-body">
    {if $images}
    <div id="slideshow{$instanceid}" class="carousel slide" data-ride="carousel" data-interval="false">
        <div class="carousel-inner" role="listbox">
            {foreach from=$images item=image key=k name=images}

            <div class="{if $dwoo.foreach.images.first}item active{else}item{/if}">
                <img src="{$image.source}" alt="{$image.title}" title="{$image.title}" class="center-block">

                {if $showdescription && $image.title}
                <div class="carousel-caption" id="description_{$instanceid}_{$k}">
                    {$image.title}
                </div>
                {/if}
            </div>
            {/foreach}
        </div>

        <div class="left carousel-control">
            <a class="carousel-control-prev" href="#slideshow{$instanceid}" role="button" data-slide="prev" title="{str tag=previous}">
                <span class="icon icon-angle-left icon-lg" role="presentation" aria-hidden="true" aria-hidden="true"></span>
                <span class="sr-only">{str tag=previous}</span>
            </a>

            <a class="carousel-control-first" href="#slideshow{$instanceid}" role="button" data-slide-to="0" title="{str tag=first}">
                <span class="icon icon-angle-double-left icon-lg" role="presentation" aria-hidden="true" aria-hidden="true"></span>
                <span class="sr-only">{str tag=first}</span>
            </a>
        </div>

        <div class="right carousel-control">
            <a class="carousel-control-next" href="#slideshow{$instanceid}" role="button" data-slide="next" title="{str tag=next}">
                <span class="icon icon-angle-right icon-lg" role="presentation" aria-hidden="true" aria-hidden="true"></span>
                <span class="sr-only">{str tag=next}</span>
            </a>
            <a class="carousel-control-last" href="#slideshow{$instanceid}" role="button" data-slide-to="{$k}" title="{str tag=last}">
                <span class="icon icon-angle-double-right icon-lg" role="presentation" aria-hidden="true" aria-hidden="true"></span>
                <span class="sr-only">{str tag=last}</span>
            </a>
        </div>
    </div>
    {else}
      {str tag=noimagesfound section=artefact.file}
    {/if}
</div>

{$comments|safe}
