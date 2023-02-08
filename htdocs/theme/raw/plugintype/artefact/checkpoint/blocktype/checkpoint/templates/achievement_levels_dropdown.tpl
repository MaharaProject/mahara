{if !$editing}
    {if $saved_achievement_level}
        {contextualhelp
            plugintype='artefact'
            pluginname='checkpoint'
            element='message'
            form="achievement_form_block$blockid"
            page='achievement_message_done'
        }
    {else}
        {contextualhelp
            plugintype='artefact'
            pluginname='checkpoint'
            element='message'
            form="achievement_form_block$blockid"
            page='achievement_message'
        }
    {/if}
    &nbsp;&nbsp;
    {$select_achievement_form|safe}
{/if}