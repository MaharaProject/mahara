{if !$editing}
     {contextualhelp
        plugintype='artefact'
        pluginname='checkpoint'
        element='message'
        form="achievement_form_block$blockid"
        page='achievement_message'
    }
    &nbsp;&nbsp;
    {$select_achievement_form|safe}
{/if}