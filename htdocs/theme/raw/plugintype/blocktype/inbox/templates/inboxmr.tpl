{if !$items}
    <div class="panel-body">
        <p class="lead text-small">{str tag=nomessages section=blocktype.inbox}</p>
    </div>
{else}
    <div id="inboxblock" class="inboxblock list-group">
        {foreach from=$items item=i}
        <div class="has-attachment panel-default collapsible list-group-item">
            {if $i->message}
                <a class="collapsed link-block{if !$i->read} unread{/if}" data-toggle="collapse" href="#message_content_{$i->type}_{$i->id}" aria-expanded="false">
                    {if $i->type == 'usermessage'}
                        <span class="icon prm icon-envelope text-default"></span>
                    {elseif $i->type == 'institutionmessage'}
                        <span class="icon prm icon-university text-default"></span>
                    {elseif $i->type == 'feedback'}
                        <span class="icon prm icon-comments text-default"></span>
                    {elseif $i->type == 'annotationfeedback'}
                        <span class="icon prm icon-comments-o text-default"></span>
                    {else}
                        <span class="icon prm icon-wrench text-default"></span>
                    {/if}
                    <span class="sr-only">{$item->strtype}</span>
                    {$i->subject|truncate:50}
                    <span class="icon icon-chevron-down collapse-indicator pull-right text-small"></span>
                </a>
            {/if}
            <div class="collapse mtm" id="message_content_{$i->type}_{$i->id}">
                {if $i->message}
                    <p>{$i->message|safe}</p>
                    {if $i->url}
                    <a href="{$WWWROOT}{$i->url}">
                        {if $i->urltext}{$i->urltext}{else}{str tag="more..."}{/if} <span class="icon icon-arrow-right mls icon-sm"></span>
                    </a>
                {/if}
                {elseif $i->url}
                    <a href="{$WWWROOT}{$i->url}">{$i->subject}</a>
                {else}
                    {$i->subject}
                {/if}
            </div>
        </div>
        {/foreach}
    </div>
    {if $desiredtypes}
    <div class="artefact-detail-link">
        <a class="link-blocktype last" href="{$WWWROOT}account/activity/index.php?type={$desiredtypes}">
        <span class="icon icon-arrow-circle-right"></span>
        {str tag=More section=blocktype.inbox}</a>
    </div>
    {/if}
{/if}
