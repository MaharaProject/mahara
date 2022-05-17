{include file="header.tpl" headertype="profile"}

<div id="view" class="view-container">
    <div id="bottom-pane" class="user-page-content">
        <div id="column-container">
            {if $restrictedview}
            <strong>{str tag=profilenotshared section=view}</strong>
            {else}
            <div class="grid-stack">
                {if !$newlayout}
                    {$viewcontent|safe}
                {/if}
            </div>
            {/if}
        </div>
    </div>
</div>

<div class="metadata text-end last-updated">
    {$lastupdatedstr}{if $visitstring}; {$visitstring}{/if}
</div>
{include file="footer.tpl"}
