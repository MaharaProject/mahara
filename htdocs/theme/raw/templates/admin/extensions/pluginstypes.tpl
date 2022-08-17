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
                <a class="btn btn-secondary btn-sm btn-group float-right" data-toggle="modal-docked" data-target="#infomodal" title="{str tag='infofor'} {$plugintype} {if $typedata.name}{$typedata.name}{else}{$plugin}{/if}" href="#" data-plugintype='{$plugintype}' data-pluginname='{$plugin}' data-type='{$type}'>
                    <span class="icon icon-cog icon-lg" role="presentation" aria-hidden="true"></span>
                    <span class="accessible-hidden sr-only">{str tag='configfor'} {$plugintype} {if $typedata.name}{$typedata.name}{else}{$plugin}{/if}</span>
                </a>
            {/if}
            {if $config.config}
                <a class="btn btn-secondary btn-sm btn-group float-right" title="{str tag='configfor'} {$plugintype} {if $typedata.name}{$typedata.name}{else}{$plugin}{/if}" href="pluginconfig.php?plugintype={$plugintype}&amp;pluginname={$plugin}&amp;type={$type}">
                    <span class="icon icon-cog" role="presentation" aria-hidden="true"></span>
                    <span class="accessible-hidden sr-only">{str tag='configfor'} {$plugintype} {if $typedata.name}{$typedata.name}{else}{$plugin}{/if}</span>
                </a>
            {/if}
            {if $typedata.typeswithconfig > 1}
            </li>
            {/if}
        {/if}
    {/foreach}
    {if $typedata.typeswithconfig > 1}
    </ul>
    {/if}