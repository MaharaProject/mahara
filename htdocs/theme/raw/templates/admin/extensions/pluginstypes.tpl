    {if $typedata.typeswithconfig > 1}
    <ul>
    {/if}
    {foreach from=$typedata.types key='type' item='config'}
        {if $config.info || $config.config}
            {if $typedata.typeswithconfig > 1}
            <li>
            {$type}
            {/if}
            {if $config.info}
                <button class="btn btn-secondary btn-sm btn-group float-end" data-bs-toggle="modal-docked" data-bs-target="#infomodal" title="{str tag='infofor'} {$plugintype} {if $typedata.name}{$typedata.name}{else}{$plugin}{/if}" type="button" data-plugintype='{$plugintype}' data-pluginname='{$plugin}' data-type='{$type}'>
                    <span class="icon icon-cog icon-lg" role="presentation" aria-hidden="true"></span>
                    <span class="accessible-hidden visually-hidden">{str tag='configfor'} {$plugintype} {if $typedata.name}{$typedata.name}{else}{$plugin}{/if}</span>
                </button>
            {/if}
            {if $config.config}
                <button class="btn btn-secondary btn-sm btn-group float-end" title="{str tag='configfor'} {$plugintype} {if $typedata.name}{$typedata.name}{else}{$plugin}{/if}" type="button" data-url="pluginconfig.php?plugintype={$plugintype}&amp;pluginname={$plugin}&amp;type={$type}">
                    <span class="icon icon-cog" role="presentation" aria-hidden="true"></span>
                    <span class="accessible-hidden visually-hidden">{str tag='configfor'} {$plugintype} {if $typedata.name}{$typedata.name}{else}{$plugin}{/if}</span>
                </button>
            {/if}
            {if $typedata.typeswithconfig > 1}
            </li>
            {/if}
        {/if}
    {/foreach}
    {if $typedata.typeswithconfig > 1}
    </ul>
    {/if}