 <ul class="artefactchooser-tabs files nav nav-tabs" role="tablist">
    {foreach from=$tabs.tabs item=displayname key=name}
    <li>
        <a class="changeowner {if $tabs.owner == $name} active{/if}" href="{$querybase|safe}owner={$name}" role="tab">
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
