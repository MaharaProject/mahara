{include file="header.tpl"}

{if $messages}
<p class="lead">{str tag='labelsubject' section='module.multirecipientnotification'} {$messages.[0]->subject}</p>
<div id="messagethread" class="collapsible-group">
{foreach from=$messages item=message name='message'}
    <div class="message-item card collapsible collapsible-group {if $dwoo.foreach.message.first}first{/if}">
        <h2 class="message-preview card-header">
            <span class="user-icon left" role="presentation" aria-hidden="true">
                <img src="{profile_icon_url user=$message->fromid maxwidth=60 maxheight=60}" alt="{$message->fromusrname}">
            </span>
            <a class="has-user-icon {if $dwoo.foreach.message.last}{else}collapsed{/if}" href="#message-{$message->id}" data-toggle="collapse" aria-expanded="{if $dwoo.foreach.message.last}true{else}false{/if}" aria-controls="#message-{$message->id}">

                {if ($message->fromusrlink != 0)}
                <a href="{$message->fromusrlink}">
                    <span class="accessible-hidden sr-only">
                        {str tag='From' section='mahara'}
                    </span>
                    {$message->fromusrname}
                </a>
                {else}
                    {$message->fromusrname}
                {/if}
                <span class="metadata">
                    - {$message->ctime|strtotime|format_date}
                </span>
                <span class="icon icon-chevron-down collapse-indicator float-right" role="presentation" aria-hidden="true"></span>
                <span class="content-preview">
                    <p class="content">{$message->message}</p>
                </span>
            </a>
        </h2>

        <div id="message-{$message->id}" class=" message-wrap collapse {if $dwoo.foreach.message.last}show{/if}">
            <div class="message-content card-body">
                <p class="recipients">
                    <strong>
                        {str tag='labelrecipients' section='module.multirecipientnotification'}
                    </strong>
                    {foreach from=$message->tousrs item=recipient key="index"}
                    {if $recipient['link']}<a href="{$recipient['link']}">{/if}
                        <span>
                        &nbsp;
                        {$recipient['display']}
                        {if ($index + 1) < count($message->tousrs)}; {/if}
                        </span>
                    {if $recipient['link']}</a>{/if}
                    {/foreach}
                </p>
                <p class="date">
                    <strong>
                        {str section='activity' tag='date'}:
                    </strong>
                    {$message->ctime|strtotime|format_date}
                </p>
                <p class="subject">
                    <strong>
                        {str tag='labelsubject' section='module.multirecipientnotification'}
                    </strong>
                    {$message->subject}
                    </a>
                </p>

                <p class="messagebody">
                    {$message->message|safe}
                </p>

            </div>
            {if $dwoo.foreach.message.last == 0}
            <div class="card-footer">
                <a href="{$link}?replyto={$message->id}&returnto={$returnto}">
                    <span class="icon icon-reply" role="presentation" aria-hidden="true"></span>
                    {str tag=reply section=module.multirecipientnotification}
                </a>
            </div>
            {/if}
        </div>
    </div>
{/foreach}
</div>
<div class="form-sendmessage card collapsible">
    <div class="card-footer">
        {$form|safe}
    </div>
</div>
{else}
    {$form|safe}
{/if}

{include file="footer.tpl"}
