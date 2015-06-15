<div class="bt-{$blocktype} panel panel-secondary {if $retractable}collapsible{/if}" id="blockinstance_{$id}">
    {if $title}
        <h3 class="title panel-heading js-heading">
            {if $retractable}
            <a data-toggle="collapse" href="#blockinstance_{$id}_target" aria-expanded="true" aria-controls="blockinstance_{$id}_target" class="outer-link"></a>
            {/if}

            <span class="inner-link">
                {if $link}
                   <a href="{$link}" class="secondary-link text-default">
                {/if}

                {$title}

                {if $link}
                   </a>
                {/if}

                {if $feedlink}
                 <a href="{$feedlink}" class="secondary-link">
                        <span class="icon-rss icon icon-large pll text-orange"></span>
                        <span class="sr-only">RSS</span>
                    </a>
                {/if}
            </span>

            {if $retractable}
            <span class="icon icon-chevron-down pls collapse-indicator pull-right inner-link"></span>
            {/if}

        </h3>
    {else}
        {if $link}
            <a href="{$link}" class="text-default btn-default btn-sm pull-right">
                {str tag=detailslinkalt section=view} 
            </a>
        {/if}
    {/if}

    <div class="{if !$title}no-heading {/if}block{if $retractable} collapse{if $retractedonload}{else} in{/if}{/if}"  id="blockinstance_{$id}_target" {if $loadbyajax}data-blocktype-ajax="{$id}"{else}data-blocktype-noajax="{$id}"{/if}>{if !$loadbyajax}{$content|safe}{/if}</div>
    
    {if $viewartefacturl} 

        <a href="{$viewartefacturl}" title="{str tag=clickformoreinformation section=view}" class="panel-footer mtl">
            {str tag=detailslinkalt section=view} 
            <span class="icon icon-arrow-circle-right mls  pull-right"></span>
        </a>

    {/if}
</div>
