<div class="collapsible-group">
{foreach from=$data item=item name='notification'}
    <div class="card collapsible notification collapsible-group {if $dwoo.foreach.notification.first}first{/if} {if $dwoo.foreach.notification.last}last{/if}">
        <h4 class="card-header">
            <label class="card-control">
                {if $item->table === 'module_multirecipient_notification'}
                <span class="control">
                    <input type="checkbox" class="tocheck" name="select-{$item->table}-{$item->id}" id="select-{$item->table}-{$item->id}">
                    <span class="sr-only">{str tag='select' section='mahara'}</span>
                </span>
                {/if}
            </label>
            <a class="collapsed" href="#notification-{$item->id}" data-toggle="collapse" aria-expanded="false" aria-controls="notification-{$item->id}" data-list="{$item->table}">
                <span class="details-group">
                    {if $item->read && $item->type == 'usermessage'}
                    <span class="icon icon-envelope type-icon" role="presentation" aria-hidden="true"></span><span class="sr-only">{$item->strtype} - {str tag='read' section='activity'}</span>
                    {elseif $item->strtype == 'usermessage'}
                    <span class="icon icon-envelope type-icon" role="presentation" aria-hidden="true"></span><span class="sr-only">{$item->strtype}</span>
                    {elseif $item->strtype == 'Institution message'}
                         <span class="icon icon-university type-icon" role="presentation" aria-hidden="true"></span>
                         <span class="sr-only">{$item->strtype}</span>
                    {else}
                    <span class="icon icon-wrench type-icon" role="presentation" aria-hidden="true"></span>
                    <span class="sr-only">{$item->strtype}</span>
                    {/if}

                    <span class="sr-only">{str section='activity' tag='subject'}</span>
                    {if !$item->read}
                        <span class="accessible-hidden sr-only">
                            {str tag='unread' section='activity'}:
                        </span>
                    {/if}

                    {$item->subject|truncate:40}

                    <span class="metadata"> -
                        <span>
                            {str section='module.multirecipientnotification' tag='touser'}:
                        </span>
                        <span class="username">
                            {if is_array($item->tousr) && count($item->tousr) > 0}
                                {assign var="tousr" value=$item->tousr[0]}
                                {if is_array($tousr['username']) && count($tousr['username']) > 0}
                                    {$tousr['username']|truncate:$maxnamestrlength}
                                {else}
                                    {$tousr['display']|truncate:$maxnamestrlength}
                                {/if}
                                <span>... ({count($item->tousr)})</span>
                                <span class="sr-only">more</span>
                            {else}
                                {assign var="tousr" value=$item->tousr[0]}
                                {$tousr['username']|truncate:$maxnamestrlength}
                            {/if}
                        </span>
                        <span class="sentdate">
                        , {$item->date}
                        </span>
                    </span>
                    <span class="icon icon-chevron-down collapse-indicator float-right" role="presentation" aria-hidden="true"></span>
                 </span>
            </a>
        </h4>
        <div id="notification-{$item->id}" class="collapse">
            {if $item->message}
            <div class="card-body {if !($item->canreplyall)} no-footer{/if}">
                <p class="tousers recipientlist">
                        <strong>
                            {str section='module.multirecipientnotification' tag='touser'}:
                        </strong>
                        {if is_array($item->tousr) && count($item->tousr) > 1}
                        <span>
                            {foreach from=$item->tousr item=tousr key=break}
                            {if ($tousr['link'])}<a href="{$tousr['link']}">{/if}
                                <span class="touser">
                                {$tousr['display']|truncate:$maxnamestrlength}
                                </span>
                            {if ($tousr['link'])}</a>{/if}
                            {/foreach}
                        </span>
                        {else}
                        <span>
                            {assign var="tousr" value=$item->tousr[0]}
                            {if $tousr['link']}<a href="{$tousr['link']}">{/if}
                                <span class="touser">
                                {$tousr['display']|truncate:$maxnamestrlength}
                                </span>
                            {if $tousr['link']}</a>{/if}
                        </span>
                        {/if}
                </p>
                <p>{$item->message|safe}</p>
            </div>
            {/if}
            {if (($item->canreply && !$item->self) || $item->canreplyall)}
            <div class="actions card-footer">
                <div class="url">
                    {if $item->canreply && !$item->self}
                    <a class="action" href="{$WWWROOT}module/multirecipientnotification/sendmessage.php?id={$item->fromusr}{if !$item->startnewthread}&replyto={$item->id}{/if}&returnto=outbox">
                        <span class="icon icon-reply left" role="presentation" aria-hidden="true"></span>
                        {str tag=reply section=module.multirecipientnotification}
                    </a>
                    {/if}
                    {if $item->canreplyall}
                    <a class="action" href="{$WWWROOT}module/multirecipientnotification/sendmessage.php?replyto={$item->id}&returnto=outbox">
                        <span class="icon icon-reply-all left" role="presentation" aria-hidden="true"></span> {str tag=replyall section=module.multirecipientnotification}
                    </a>
                    {/if}
                </div>
            </div>
            {/if}
        </div>
    </div>
{/foreach}
</div>
