 <ul class="artefactchooser-tabs files nav nav-tabs">
    {foreach from=$tabs.tabs item=displayname key=name}
    <li{if $tabs.owner == $name} class="active"{/if}>
        <a class="changeowner" href="{$querybase}owner={$name}">
            {$displayname}
            <span class="accessible-hidden sr-only">
                ({str tag=tab}
                {if $tabs.owner == $name}
                {str tag=selected}{/if})
            </span>
        </a>
    </li>
    {/foreach}
</ul>
