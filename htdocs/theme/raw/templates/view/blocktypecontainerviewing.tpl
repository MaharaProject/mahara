<div class="bt-{$blocktype} panel panel-secondary {if $retractable}collapsible{/if}" id="blockinstance_{$id}">
    {if $title}
        <h3 class="title panel-heading js-heading">
            {if $retractable}
            <a data-toggle="collapse" href="#blockinstance_{$id}_target" aria-expanded="true" aria-controls="blockinstance_{$id}_target" class="{if $retractedonload}collapsed {/if}outer-link"></a>
            {/if}

            {$title}
            <span class="inner-link">
                {if $link}
                    <a href="{$link}" class="detail-link secondary-link text-small text-lowercase pls">{str tag=detailslinkalt section=view}</a>
                {elseif $viewartefacturl}
                    <a href="{$viewartefacturl}" class="detail-link secondary-link text-small text-lowercase pls">{str tag=detailslinkalt section=view}</a>
                {/if}
            </span>

            {if $feedlink}
                <a href="{$feedlink}" class="secondary-link inner-link">
                    <span class="icon-rss icon icon-large pll text-orange"></span>
                    <span class="sr-only">RSS</span>
                </a>
            {/if}


            {if $retractable}
            <span class="icon icon-chevron-down pls collapse-indicator pull-right"></span>
            {/if}

        </h3>
    {else}
        {if $link}
            <a href="{$link}" class="text-default btn-default btn-sm pull-right">
                {str tag=detailslinkalt section=view}
            </a>
        {/if}
    {/if}

    <div class="{if !$title}no-heading {/if}block{if $retractable} collapse{if $retractedonload}{else} in{/if}{/if}"  id="blockinstance_{$id}_target" {if $loadbyajax}data-blocktype-ajax="{$id}"{else}data-blocktype-noajax="{$id}"{/if}>
        {if !$loadbyajax}{$content|safe}{/if}
    </div>
</div>
