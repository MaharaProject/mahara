{include file='header.tpl'}
<p class="lead">{str tag='pluginexplainaddremove'} {str tag='pluginexplainartefactblocktypes'}</p>

<div class="card-items js-masonry" data-masonry-options='{ "itemSelector": ".card" }'>
{foreach from=$plugins key='plugintype' item='plugins'}
    <div class="card">
        <h2 class="card-header">{str tag='plugintype'}: {$plugintype}</h2>
        {assign var="installed" value=$plugins.installed}
        {assign var="notinstalled" value=$plugins.notinstalled}

        {if $notinstalled}
            <ul class="notinstalled list-group" id="{$plugintype}.notinstalled">
                <li class="list-group-item list-group-item-heading list-group-item-warning">
                    <h3 class="list-group-item-heading h4">{str tag='notinstalledplugins'}</h3>
                </li>

                {foreach from=$notinstalled key='plugin' item='data'}
                    <li class="list-group-item list-group-item-danger" id="{$plugintype}.{$plugin}">
                        {if $data.name}{$data.name}{else}{$plugin}{/if}
                        {if $data.notinstallable}
                            {str tag='notinstallable'}: {$data.notinstallable}
                        {else}
                            <span id="{$plugintype}.{$plugin}.install">(<a href="" onClick="{$installlink}('{$plugintype}.{$plugin}'); return false;">{str tag='install' section='admin'} <span class="accessible-hidden sr-only">{$plugintype} {if $data.name}{$data.name}{else}{$plugin}{/if}</span></a>)</span>
                        {/if}
                        <span id="{$plugintype}.{$plugin}.message"></span>
                    </li>
                {/foreach}
            </ul>
        {/if}


        <ul class="list-group" id="{$plugintype}.installed">
            <li class="list-group-item list-group-item-heading">
                <h3 class="list-group-item-heading h4">{str tag='installedplugins'}</h3>
            </li>
            {foreach from=$installed key='plugin' item='data'}
                <li class="list-group-item{if !$data.active} list-group-item-warning{/if}" id="{$plugintype}.{$plugin}">
                    <div class="list-group-item-heading">
                        {if $data.name}{$data.name}{else}{$plugin}{/if}
                        {if $data.deprecated}{str tag=deprecated section=admin}{/if}
                        <div class="btn-group btn-group-top">
                        {if $data.config}
                            <a class="btn btn-secondary float-left btn-group-item" title="{str tag='configfor'} {$plugintype} {if $data.name}{$data.name}{else}{$plugin}{/if}" href="pluginconfig.php?plugintype={$plugintype}&amp;pluginname={$plugin}">
                                 <span class="icon icon-cog icon-lg" role="presentation" aria-hidden="true"></span>
                                 <span class="accessible-hidden sr-only ">{str tag='configfor'} {$plugintype} {if $data.name}{$data.name}{else}{$plugin}{/if}</span>
                            </a>
                        {/if}
                        {if $data.activateform}
                            {$data.activateform|safe}
                        {/if}
                        </div>
                    </div>

                    {if $data.types}

                        <ul>
                        {foreach from=$data.types key='type' item='config'}
                            <li>
                            {$type}
                            {if $config}
                                <a class="btn btn-secondary btn-sm btn-group float-right" title="{str tag='configfor'} {$plugintype} {if $data.name}{$data.name}{else}{$plugin}{/if}" href="pluginconfig.php?plugintype={$plugintype}&amp;pluginname={$plugin}&amp;type={$type}">
                                    <span class="icon icon-cog icon-lg" role="presentation" aria-hidden="true"></span>
                                    <span class="accessible-hidden sr-only">{str tag='configfor'} {$plugintype} {if $data.name}{$data.name}{else}{$plugin}{/if}</span>
                                </a>
                            {/if}
                            </li>
                        {/foreach}
                        </ul>

                    {/if}
                </li>
            {/foreach}
        </ul>
    </div>
{/foreach}
</div>


{include file='footer.tpl'}
