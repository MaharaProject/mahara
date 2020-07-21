<div class="bt-{$blocktype} gridstackblock card card-secondary {$cardicontype} clearfix {if $retractable}collapsible{/if}" id="blockinstance_{$id}">
    {if !$editing && $blockheader && !$versioning && !$peerroleonly}
        {include file='header/block-comments-details-header.tpl' artefactid=$artefactid blockid=$blockid commentcount=$commentcount}
    {/if}
    {if $showquickedit}
        {include file='header/block-quickedit-header.tpl' blockid=$blockid}
    {/if}
    {if $title}
        <h2 class="title card-header js-heading">
            {if $retractable}
            <a data-toggle="collapse" href="#blockinstance_{$id}_target{if $versioning}_{$versioning->version}{/if}" aria-expanded="{if $retractedonload}false{else}true{/if}" aria-controls="blockinstance_{$id}_target{if $versioning}_{$versioning->version}{/if}" class="outer-link{if $retractedonload} collapsed{/if}"></a>
            {/if}

            <span class="collapse-inline">
                {$title}
                {if $feedlink}
                    <a href="{$feedlink}" class="secondary-link inner-link">
                        <span class="icon-rss icon icon-lg mahara-rss-icon right" role="presentation" aria-hidden="true"></span>
                        <span class="sr-only">RSS</span>
                    </a>
                {/if}
            </span>

            {if $retractable}
            <span class="icon icon-chevron-up collapse-indicator float-right" role="presentation" aria-hidden="true"></span>
            {/if}

        </h2>
    {else}
        {if $link}
            <a href="{$link}" class="text-default btn-secondary btn-sm float-right">
                {str tag=detailslinkalt section=view}
            </a>
        {/if}
    {/if}

    <div class="{if !$title}no-heading {/if}block{if $retractable} collapse{if $retractedonload}{else} show{/if}{/if}{if $draft} draft{/if}"  id="blockinstance_{$id}_target{if $versioning}_{$versioning->version}{/if}" {if $loadbyajax}data-blocktype-ajax="{$id}"{else}data-blocktype-noajax="{$id}"{/if}>
        {if $draft}
        <span class="sr-only">{str tag='draft' section='artefact.blog'}</span>
        {/if}
        {if !$loadbyajax}
            {$content|safe}
        {else}
            <div id="loadingicon{$id}"><span class="icon icon-spinner icon-pulse"></span> {str tag=loading}</div>
        {/if}
    </div>
</div>
