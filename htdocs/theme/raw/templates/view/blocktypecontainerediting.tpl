<div class="bt-{$blocktype}-editor js-blockinstance blockinstance gridstackblock card card-secondary clearfix {if $configure} configure{elseif $retractable} retractable{/if}" data-id="{$id}" id="blockinstance_{$id}{if $configure}_configure{/if}">
    <h3 class="card-header js-heading drag-handle {if !$title}card-header-placeholder{/if} access-drop-handle" title="{$strmovetitletexttooltip}">
        <span class="icon icon-arrows-alt move-indicator" role="presentation" aria-hidden="true"></span>
        <span class="blockinstance-header">
            {if $configure}
                <div class="icon icon-sm icon-{$cssicon} {$cssicontype}" title="{$blocktypename}"></div>
                {$configtitle}: {str tag=Configure section=view}{else}{$title|default:"[$strnotitle]"}
            {/if}
            {if $retractable && !$configure}
            <span class="icon icon-chevron-down text-watermark icon-sm" role="presentation" aria-hidden="true"></span>
            <span class="sr-only">{str tag=retractable section=view}</span>
            {/if}
        </span>
        <span class="blockinstance-controls">
            <button class="keyboardmovebutton btn btn-secondary d-none sr-only" name="action_moveblockinstance_id_{$id}" alt="{$strmovetitletext}"  data-id="{$id}">
                {$strmovetitletext}
            </button>
            <span class="float-right btn-group btn-group-top">
                {if $configurable && !$configure}
                <button class="configurebutton btn btn-inverse btn-sm" name="action_configureblockinstance_id_{$id}" alt="{$strconfigtitletext}" data-id="{$id}">
                    <span class="icon icon-cog icon-lg" role="presentation" aria-hidden="true" title="{$strconfigtitletexttooltip}"></span>
                    <span class="sr-only">{$strconfigtitletext}</span>
                </button>
                {/if}
                {if !$lockblocks}
                <button class="deletebutton btn btn-inverse btn-sm" name="action_removeblockinstance_id_{$id}" alt="{$strremovetitletext}" data-id="{$id}">
                    <span class="icon icon-trash-alt text-danger icon-lg" role="presentation" aria-hidden="true" title="{$strremovetitletexttooltip}"></span>
                    <span class="sr-only">{$strremovetitletext}</span>
                </button>
                {/if}
            </span>
        </span>
    </h3>
    <span class="sr-only">{str tag=blocktypeis section=view arg1=$blocktype}</span>
    <div class="block blockinstance-content js-blockinstance-content {if $draft}draft {/if}">
        {$content|safe}
    </div>
</div>
