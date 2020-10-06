<div class="bt-{$blocktype}-editor js-blockinstance blockinstance gridstackblock card card-secondary clearfix {if $configure} configure{elseif $retractable} collapsible{/if}" data-id="{$id}" id="blockinstance_{$id}{if $configure}_configure{/if}">
    <h2 class="card-header js-heading drag-handle {if !$title}card-header-placeholder{/if} access-drop-handle" title="{$strmovetitletexttooltip}">
        <span class="icon icon-arrows-alt icon-lg move-indicator" role="presentation" aria-hidden="true"></span>
        <span class="blockinstance-header{if $retractable && $retractedonload} retracted{/if}">
            {if $configure}
                <div class="icon icon-sm icon-{$cssicon} {$cssicontype}" title="{$blocktypename}"></div>
                {$configtitle}: {str tag=Configure section=view}{else}{$title|default:"[$strnotitle]"}
            {/if}
        </span>
        <span class="blockinstance-controls">
            <button class="keyboardmovebutton btn btn-secondary d-none sr-only" name="action_moveblockinstance_id_{$id}" alt="{$strmovetitletext}"  data-id="{$id}">
                {$strmovetitletext}
            </button>
            <span class="float-right btn-group btn-group-top">
                {if $retractable && !$configure}
                <a data-toggle="collapse" href="#blockinstance_{$id}_target" aria-expanded="{if $retractedonload}false{else}true{/if}" aria-controls="blockinstance_{$id}_target" class="collapse-arrow{if $retractedonload} collapsed{/if}">
                    <span class="icon icon-chevron-up collapse-indicator pull-right inner-link" role="presentation" aria-hidden="true"></span>
                    <span class="sr-only">{str tag=retractable section=view}</span>
                </a>
                {/if}
                {if $configurable && !$configure}
                <button class="configurebutton btn btn-inverse btn-sm" name="action_configureblockinstance_id_{$id}" alt="{$strconfigtitletext}" data-id="{$id}">
                    <span class="icon icon-cog" role="presentation" aria-hidden="true" title="{$strconfigtitletexttooltip}"></span>
                    <span class="sr-only">{$strconfigtitletext}</span>
                </button>
                {/if}
                {if !$lockblocks}
                <button class="deletebutton btn btn-inverse btn-sm" name="action_removeblockinstance_id_{$id}" alt="{$strremovetitletext}" data-id="{$id}">
                    <span class="icon icon-trash-alt text-danger" role="presentation" aria-hidden="true" title="{$strremovetitletexttooltip}"></span>
                    <span class="sr-only">{$strremovetitletext}</span>
                </button>
                {/if}

            </span>

        </span>
    </h2>
    <span class="sr-only">{str tag=blocktypeis section=view arg1=$blocktype}</span>
    <div class="block blockinstance-content js-blockinstance-content {if $draft} draft{/if}{if $retractable} collapse{if $retractedonload} {else} show{/if}{/if}" id="blockinstance_{$id}_target" {if $loadbyajax}data-blocktype-ajax="{$id}"{else}data-blocktype-noajax="{$id}"{/if}>
        {$content|safe}
    </div>
</div>
