<div class="panel-body">
    {$text|clean_html|safe}

    {if $artefact->get('tags')}
    <div class="tags">{str tag=tags}: {list_tags owner=$artefact->get('owner') tags=$artefact->get('tags')}</div>
    {/if}
</div>

    {if $annotationfeedbackcount || $annotationfeedbackcount == 0}
        {$annotationfeedback|safe}
    {/if}


{if $addannotationscript}
    <script type="application/javascript" src="{$addannotationscript}"></script>
{/if}
