<div class="card-body flush">
    {$text|clean_html|safe}

    {if $artefact->get('tags')}
    <div class="tags">
        <strong>{str tag=tags}:</strong> {list_tags owner=$artefact->get('owner') tags=$artefact->get('tags')}
    </div>
    {/if}

    {if $annotationfeedbackcount || $annotationfeedbackcount == 0}
        {$annotationfeedback|safe}
    {/if}
</div>

{if $addannotationscript}
    <script src="{$addannotationscript}"></script>
{/if}
