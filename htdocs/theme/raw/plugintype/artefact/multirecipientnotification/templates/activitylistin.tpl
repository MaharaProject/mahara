<div class="collapsible-group">
{foreach from=$data item=item name='notification'}
    <div class="panel collapsible notification collapsible-group  {if !$item->read}panel-primary js-panel-unread{else}panel-default{/if} {if $dwoo.foreach.notification.first}first{/if} {if $dwoo.foreach.notification.last}last{/if} ">
        <h4 class="panel-heading">
            <label class="panel-control">
                <span class="control prl {if !$item->read}unread{/if}">
                    <input type="checkbox" class="tocheck" name="select-{$item->table}-{$item->id}" id="select-{$item->table}-{$item->id}">
                    <span class="sr-only">{str tag='select' section='mahara'}</span>

                </span>
            </label>

            <a class="collapsed" href="#notification-{$item->table}-{$item->id}" data-id="{$item->id}" data-table="{$item->table}" data-toggle="collapse" aria-expanded="1" aria-controls="notification-{$item->table}-{$item->id}">
                <span class="details-group">
                    {if $item->read && $item->type == 'usermessage'}
                        <span class="icon icon-envelope type-icon prxl plxl"></span>
                        <span class="sr-only">{$item->strtype} - {str tag='read' section='activity'}</span>
                    {else}
                        {if $item->type == 'usermessage'}
                            <span class="icon icon-envelope type-icon prxl plxl"></span>
                        {elseif $item->type == 'institutionmessage'}
                            <span class="icon icon-university type-icon prxl plxl"></span>
                        {elseif $item->type == 'feedback'}
                            <span class="icon icon-comments type-icon prxl plxl"></span>
                        {elseif $item->type == 'annotationfeedback'}
                            <span class="icon icon-comments-o type-icon prxl plxl"></span>
                        {else}
                            <span class="icon icon-wrench type-icon prxl plxl"></span>
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
                            {str section='artefact.multirecipientnotification 'tag='fromuser'}:
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
                    <span class="icon icon-chevron-down pls collapse-indicator pull-right"></span>
                </span>
            </a>
        </h4>
        <div id="notification-{$item->table}-{$item->id}" class="collapse">
            {if $item->message}
            <div class="content panel-body {if !($item->canreply || $item->canreplyall)}mbl no-footer{/if}">
                {if ($item->fromusr != 0)}
                <p class="fromusers">
                    <strong>
                        {str section='artefact.multirecipientnotification' tag='fromuser'}:
                    </strong>
                    {if ($item->fromusrlink)}
                    <span class="fromusers prm">
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
                        {str section='artefact.multirecipientnotification' tag='fromuser'}:
                    </strong>
                    <span>{str tag="system"}</span>
                </p>
                {/if}
                <p class="tousers">
                    <strong>
                        {str section='artefact.multirecipientnotification' tag='touser'}:
                    </strong>
                    {if $item->return}
                    <span class="tousers">
                        {foreach from=$item->tousr item=tousr key=break}
                        {if ($tousr['link'])}
                        <a class="prm" href="{$tousr['link']}">
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
                <p>{$item->message|safe}</p>
                {if $item->url}
                <a class="action" href="{$WWWROOT}{$item->url}">
                    <span class="icon icon-arrow-right"></span>
                    {if $item->urltext}
                        {$item->urltext}
                    {else}
                        <span class="sr-only">{str tag="more..."}</span>
                    {/if}
                </a>
                {/if}
            </div>
            {/if}

            {if ($item->canreply || $item->canreplyall)}
            <div class="actions panel-footer mbl">
                <div class="url">
                    {if $item->canreply}
                    <a class="action" href="{$WWWROOT}artefact/multirecipientnotification/sendmessage.php?id={$item->fromusr}{if !$item->startnewthread}&replyto={$item->id}{/if}&returnto=outbox">
                        <span class="icon icon-reply"></span>
                        {str tag=reply section=artefact.multirecipientnotification}
                    </a>
                    {/if}
                    {if $item->canreplyall}
                    <a class="action" href="{$WWWROOT}artefact/multirecipientnotification/sendmessage.php?replyto={$item->id}&returnto=outbox">
                        <span class="icon icon-reply-all"></span> {str tag=replyall section=artefact.multirecipientnotification}
                    </a>
                    {/if}
                </div>
            </div>
            {/if}
        </div>
    </div>
{/foreach}
</div>
