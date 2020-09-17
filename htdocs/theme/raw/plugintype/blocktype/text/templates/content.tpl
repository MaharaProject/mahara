{if $instructions}
<div class="blockinstructions last form-group collapsible-group small-group">
    <fieldset class="pieform-fieldset collapsible collapsible-small">
        <legend>
            <a href="#dropdown_{$blockid}" data-toggle="collapse" aria-expanded="{if $editing}true{else}false{/if}" aria-controls="dropdown" class="{if $editing}show{else}collapsed{/if} linkinstructions">
                {str tag='instructions' section='view'}
                <span class="icon icon-chevron-down collapse-indicator right float-right"> </span>
            </a>
        </legend>
        <div class="fieldset-body {if $editing}show{else}collapse{/if} " id="dropdown_{$blockid}">
            {$instructions|clean_html|safe}
        </div>
</fieldset>
</div>
{/if}
{if $text}
<div class="textblock card-body flush">
{$text|clean_html|safe}
</div>
{/if}
