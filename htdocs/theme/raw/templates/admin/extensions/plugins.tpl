{include file='header.tpl'}
<p class="lead">{str tag='pluginexplainaddremove'} {str tag='pluginexplainartefactblocktypes'}</p>

<div class="card-items js-masonry extensions" data-masonry-options='{ "itemSelector": ".card" }'>
{foreach from=$plugins key='plugintype' item='plugins'}
    <div class="card">
        <h2 class="card-header">{str tag='plugintype'}: {$plugintype}
        {if $plugins.configure}
            <div class="btn-group btn-group-top">
                <a class="btn btn-secondary float-left btn-group-item" title="{str tag='configfor'} {$plugintype}" href="plugintypeconfig.php?plugintype={$plugintype}">
                    <span class="icon icon-cog icon-lg" role="presentation" aria-hidden="true"></span>
                    <span class="accessible-hidden sr-only ">{str tag='configfor'} {$plugintype}</span>
                </a>
            </div>
        {/if}
        </h2>
        {assign var="installed" value=$plugins.installed}
        {assign var="notinstalled" value=$plugins.notinstalled}

        {if $notinstalled}
            <ul class="notinstalled list-group plugins-list-group" id="{$plugintype}.notinstalled">
                <li class="list-group-item list-group-item-heading list-group-item-warning">
                    <h3 class="list-group-item-heading h4">{str tag='notinstalledplugins'}</h3>
                </li>

                {foreach from=$notinstalled key='plugin' item='data'}
                    <li class="list-group-item list-group-item-danger" id="{$plugintype}.{$plugin}">
                        {if $data.name}{$data.name}{else}{$plugin}{/if}
                        {if $data.notinstallable}
                            {str tag='notinstallable'}: {$data.notinstallable|clean_html|safe}
                        {else}
                            <span id="{$plugintype}.{$plugin}.install">(<a href="" onClick="{$installlink}('{$plugintype}.{$plugin}'); return false;">{str tag='install' section='admin'}<span class="accessible-hidden sr-only"> {$plugintype} {if $data.name}{$data.name}{else}{$plugin}{/if}</span></a>)</span>
                        {/if}
                        {if $data.dependencies}
                            {if $data.dependencies.needs}<div class="notes">{$data.dependencies.needs|safe}</div>{/if}
                        {/if}
                        <span id="{$plugintype}.{$plugin}.message"></span>
                    </li>
                {/foreach}
            </ul>
        {/if}


        <ul class="list-group plugins-list-group" id="{$plugintype}.installed">
            <li class="list-group-item list-group-item-heading">
                <h3 class="list-group-item-heading h4">{str tag='installedplugins'}</h3>
            </li>
            {foreach from=$installed key='plugin' item='data'}
                <li class="list-group-item{if !$data.active} list-group-item-warning{/if}" id="{$plugintype}.{$plugin}">
                    <div class="list-group-item-heading">
                        {if $data.name}{$data.name}{else}{$plugin}{/if}
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
                        {if $data.deprecated}{if gettype($data.deprecated) eq 'string'}<div class="alert alert-warning text-small">{$data.deprecated}</div>{else}{str tag=deprecated section=admin}{/if}{/if}
                        {if $data.dependencies}
                            {if $data.dependencies.needs}<div class="notes">{$data.dependencies.needs|safe}</div>{/if}
                            {if $data.dependencies.requires}<div class="danger">{$data.dependencies.requires|safe}</div>{/if}
                        {/if}
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
