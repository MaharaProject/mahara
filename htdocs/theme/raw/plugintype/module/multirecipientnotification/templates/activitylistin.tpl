<div class="collapsible-group">
{foreach from=$data item=item name='notification'}
    <div class="card collapsible notification collapsible-group  {if !$item->read}card text-weight-bold js-card-unread{else}card{/if} {if $dwoo.foreach.notification.first}first{/if} {if $dwoo.foreach.notification.last}last{/if} ">
        <div class="card-header">
            <label class="card-control">
                <span class="control {if !$item->read}unread{/if}">
                    <input type="checkbox" class="tocheck" name="select-{$item->table}-{$item->id}" id="select-{$item->table}-{$item->id}">
                    <span class="sr-only">{str tag='select' section='mahara'}</span>

                </span>
            </label>

            <a class="collapsed" href="#notification-{$item->table}-{$item->id}" data-id="{$item->id}" data-list="{$item->table}" data-toggle="collapse" aria-expanded="false" aria-controls="notification-{$item->table}-{$item->id}">
                <span class="details-group">
                    {if $item->read && $item->type == 'usermessage'}
                        <span class="icon icon-envelope type-icon" role="presentation" aria-hidden="true"></span>
                        <span class="sr-only">{$item->strtype} - {str tag='read' section='activity'}</span>
                    {else}
                        {if $item->type == 'usermessage'}
                            <span class="icon icon-envelope type-icon" role="presentation" aria-hidden="true"></span>
                        {elseif $item->type == 'institutionmessage'}
                            <span class="icon icon-university type-icon" role="presentation" aria-hidden="true"></span>
                        {elseif $item->type == 'feedback'}
                            <span class="icon icon-comments type-icon" role="presentation" aria-hidden="true"></span>
                        {elseif $item->type == 'annotationfeedback'}
                            <span class="icon icon-comments-o type-icon" role="presentation" aria-hidden="true"></span>
                        {elseif $item->type == 'wallpost'}
                            <span class="icon icon-wall type-icon" role="presentation" aria-hidden="true"></span>
                        {else}
                            <span class="icon icon-wrench type-icon" role="presentation" aria-hidden="true"></span>
                        {/if}

                        <span class="sr-only">{$item->strtype}</span>
                    {/if}


                    <span class="sr-only">{str section='activity' tag='subject'}</span>
                    {if !$item->read}
                        <span class="accessible-hidden sr-only">
                            {str tag='unread' section='activity'}:
                        </span>
                    {/if}
                    <span class="subject">
                        {$item->subject|truncate:80}
                    </span>

                    <span class="metadata">
                        <span class="sr-only">
                            {str section='module.multirecipientnotification 'tag='fromuser'}:
                        </span>
                        {if ($item->fromusr != 0)}
                            {if ($item->fromusrlink)}
                                <span class="username">
                                    {/if}
                                    - {$item->fromusr|display_name|truncate:$maxnamestrlength}
                                    {if ($item->fromusrlink)}
                                </span>
                            {/if}
                        {else}
                            <span class="username">
                                - {str tag="system"}
                            </span>
                        {/if}
                        <span class="sentdate ">
                            , {$item->date}
                        </span>
                    </span>
                    <span class="icon icon-chevron-down collapse-indicator float-right" role="presentation" aria-hidden="true"></span>
                </span>
            </a>
        </div>
        <div id="notification-{$item->table}-{$item->id}" class="collapse">
            {if $item->message}
            <div class="card-body {if !($item->canreply || $item->canreplyall)} no-footer{/if}">
                {if ($item->fromusr != 0)}
                <p class="fromusers">
                    <strong>
                        {str section='module.multirecipientnotification' tag='fromuser'}:
                    </strong>
                    {if ($item->fromusrlink)}
                    <span class="fromuser">
                        <a href="{$item->fromusrlink}">
                            {/if}
                            {$item->fromusr|display_name|truncate:$maxnamestrlength}
                            {if ($item->fromusrlink)}
                        </a>
                    </span>
                    {/if}
                </p>
                    {else}
                <p class="fromusers">
                    <strong>
                        {str section='module.multirecipientnotification' tag='fromuser'}:
                    </strong>
                    <span>{str tag="system"}</span>
                </p>
                {/if}
                <p class="tousers">
                    <strong>
                        {str section='module.multirecipientnotification' tag='touser'}:
                    </strong>
                    {if $item->canreplyall}
                    <span class="tousers">
                        {foreach from=$item->tousr item=tousr key=break}
                        {if ($tousr['link'])}
                        <a class="tousers" href="{$tousr['link']}">
                            {/if}
                            {$tousr['display']|truncate:$maxnamestrlength}
                            {if ($tousr['link'])}
                        </a>{/if}
                    {/foreach}
                    </span>
                    {else}
                    <span>
                        {assign var="tousr" value=$item->tousr[0]}
                    </span>
                    {if ($tousr['link'])}
                    <a href="{$tousr['link']}">
                        {/if}
                        <span>
                            {$tousr['display']|truncate:$maxnamestrlength}
                        </span>
                        {if ($tousr['link'])}
                    </a>{/if}
                    {/if}
                </p>
                <p>
                {if ($item->fromusr != 0)}
                    {$item->message|safe|clean_html}
                {else}
                    {$item->message|safe}
                {/if}
                </p>
                {if $item->url}
                <a class="action" href="{$WWWROOT}{$item->url}">
                    {if $item->urltext}
                        <span class="text-small">{$item->urltext}</span>
                    {else}
                        <span class="text-small">{str tag="more..."}</span>
                    {/if}
                    <span class="icon icon-arrow-right right" role="presentation" aria-hidden="true"></span>
                </a>
                {/if}
            </div>
            {/if}

            {if ($item->canreply || $item->canreplyall)}
            <div class="actions card-footer">
                <div class="url">
                    {if $item->canreply}
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
