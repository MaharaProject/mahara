{if $tabs.subtabs}
<div class="btn-group ownersubtab">
    <button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
        {str tag="tab$tabs.owner"}
    <span class="icon icon-caret-down right" role="presentation" aria-hidden="true"></span>
    </button>
    <ul class="artefactchooser-subtabs dropdown-menu" role="menu">
        {foreach from=$tabs.subtabs item=displayname key=ownerid name=subtab}
        <li class="dropdown-item {if $tabs.ownerid == $ownerid}active {/if}{if !$dwoo.foreach.subtab.last} showrightborder{/if}">
            <a class="changeowner" href="{$querybase|safe}owner={$tabs.owner}&ownerid={$ownerid}">
            {$displayname}
            </a>
        </li>
        {/foreach}
    </ul>
    <div class="artefactchooser-subtabs-selected">
        <em class="js-dropdown-context text-midtone text-small">
        {foreach from=$tabs.subtabs item=displayname key=ownerid name=subtab}
            {if $tabs.ownerid == $ownerid}({$displayname}){/if}
        {/foreach}
        </em>
    </div>
</div>
{/if}
