<div class="bt-{$blocktype} panel panel-secondary clearfix {if $retractable}collapsible{/if}" id="blockinstance_{$id}">
    {if $title}
        <h3 class="title panel-heading js-heading">
            {if $retractable}
            <a data-toggle="collapse" href="#blockinstance_{$id}_target" aria-expanded="{if $retractedonload}false{else}true{/if}" aria-controls="blockinstance_{$id}_target" class="outer-link{if $retractedonload} collapsed{/if}"></a>
            {/if}

            {$title}
            {if $feedlink}
                <a href="{$feedlink}" class="secondary-link inner-link">
                    <span class="icon-rss icon icon-large mahara-rss-icon right" role="presentation" aria-hidden="true"></span>
                    <span class="sr-only">RSS</span>
                </a>
            {/if}


            {if $retractable}
            <span class="icon icon-chevron-up collapse-indicator pull-right inner-link" role="presentation" aria-hidden="true"></span>
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

        {if $link || $viewartefacturl}

            {if $link}
                <a href="{$link}" class="detail-link link-blocktype"><span class="icon icon-link" role="presentation" aria-hidden="true"></span> {str tag=detailslinkalt section=view}</a>
            {elseif $viewartefacturl}
                <a href="{$viewartefacturl}" class="detail-link link-blocktype"><span class="icon icon-link" role="presentation" aria-hidden="true"></span> {str tag=detailslinkalt section=view}</a>
            {/if}
        {/if}
    </div>
</div>
