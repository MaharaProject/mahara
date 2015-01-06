{$text|clean_html|safe}

{if $artefact->get('tags')}
<div class="tags">{str tag=tags}: {list_tags owner=$artefact->get('owner') tags=$artefact->get('tags')}</div>
{/if}

{if $addannotationscript}
    <script type="text/javascript" src="{$addannotationscript}"></script>
{/if}

{if $annotationfeedbackcount || $annotationfeedbackcount == 0}
    {$annotationfeedback|safe}
{/if}
