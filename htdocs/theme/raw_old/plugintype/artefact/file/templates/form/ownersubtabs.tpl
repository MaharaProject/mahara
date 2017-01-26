{if $tabs.subtabs}
<div class="btn-group">
    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
        {str tag=groups}
    <span class="icon icon-caret-down right" role="presentation" aria-hidden="true"></span>
    </button>
    <ul class="artefactchooser-subtabs dropdown-menu" role="menu">
        {foreach from=$tabs.subtabs item=displayname key=ownerid name=subtab}
        <li class="{if $tabs.ownerid == $ownerid}active {/if}{if !$dwoo.foreach.subtab.last} showrightborder{/if}">
            <a class="changeowner" href="{$querybase}owner={$tabs.owner}&ownerid={$ownerid}">
            {$displayname}
            </a>
        </li>
        {/foreach}
    </ul>
</div>
{/if}
