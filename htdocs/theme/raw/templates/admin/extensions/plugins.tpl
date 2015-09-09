{include file='header.tpl'}
<p class="lead">{str tag='pluginexplainaddremove'} {str tag='pluginexplainartefactblocktypes'}</p>

<div class="panel-items js-masonry" data-masonry-options='{ "itemSelector": ".panel" }'>
{foreach from=$plugins key='plugintype' item='plugins'}
    <div class="panel panel-default">
        <h2 class="panel-heading">{str tag='plugintype'}: {$plugintype}</h2>
        {assign var="installed" value=$plugins.installed}
        {assign var="notinstalled" value=$plugins.notinstalled}

        {if $notinstalled}
            <ul class="notinstalled list-group" id="{$plugintype}.notinstalled">
                <li class="list-group-item list-group-item-heading mb0 list-group-item-warning">
                    <h3 class="list-group-item-heading h4">{str tag='notinstalledplugins'}</h3>
                </li>

                {foreach from=$notinstalled key='plugin' item='data'}
                    <li class="list-group-item list-group-item-danger" id="{$plugintype}.{$plugin}">{$plugin}
                        {if $data.notinstallable}
                            {str tag='notinstallable'}: {$data.notinstallable}
                        {else}
                            <span id="{$plugintype}.{$plugin}.install">(<a href="" onClick="{$installlink}('{$plugintype}.{$plugin}'); return false;">{str tag='install' section='admin'} <span class="accessible-hidden sr-only">{$plugintype} {$plugin}</span></a>)</span>
                        {/if}
                        <span id="{$plugintype}.{$plugin}.message"></span>
                    </li>
                {/foreach}
            </ul>
        {/if}


        <ul class="list-group" id="{$plugintype}.installed">
            <li class="list-group-item list-group-item-heading mb0">
                <h3 class="list-group-item-heading h4">{str tag='installedplugins'}</h3>
            </li>
            {foreach from=$installed key='plugin' item='data'}
                <li class="list-group-item{if !$data.active} list-group-item-warning{/if}" id="{$plugintype}.{$plugin}">
                    <div class="list-group-item-heading">
                        {$plugin}
                        {if $data.activateform}
                            <div class="btn-group btn-group-top">
                            {$data.activateform|safe}
                        {/if}
                        {if $data.config}
                            {if !$data.activateform} <div class="btn-group btn-group-top"> {/if}
                            <a class="btn btn-default pull-left" title="{str tag='configfor'} {$plugintype} {$plugin}" href="pluginconfig.php?plugintype={$plugintype}&amp;pluginname={$plugin}">
                                 <span class="icon icon-cog icon-lg"></span>
                                 <span class="accessible-hidden sr-only ">{str tag='configfor'} {$plugintype} {$plugin}</span>
                            </a>
                        {/if}

                        {if $data.config || $data.activateform}
                            </div>
                        {/if}

                    </div>

                    {if $data.types}

                        <ul>
                        {foreach from=$data.types key='type' item='config'}
                            <li>
                            {$type}
                            {if $config}
                            <!-- <div class="btn-group btn-group-top"> -->
                                <a class="btn btn-default btn-xs btn-group" title="{str tag='configfor'} {$plugintype} {$plugin}" href="pluginconfig.php?plugintype={$plugintype}&amp;pluginname={$plugin}&amp;type={$type}">
                                    <span class="icon icon-cog icon-lg"></span>
                                    <span class="accessible-hidden sr-only">{str tag='configfor'} {$plugintype} {$plugin}</span>
                                </a>
                            <!-- </div> -->
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
