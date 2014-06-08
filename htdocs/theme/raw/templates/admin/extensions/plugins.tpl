{include file='header.tpl'}

<div id="adminplugin">
    <b>{str tag='pluginexplainaddremove'}
    <br/><br/>{str tag='pluginexplainartefactblocktypes'}<br/><br/></b>
    <ul class="adminpluginstypes">
    {foreach from=$plugins key='plugintype' item='plugins'}
        <li><h4>{str tag='plugintype'}: {$plugintype}</h4>
        {assign var="installed" value=$plugins.installed}
        {assign var="notinstalled" value=$plugins.notinstalled}
            <ul>
                {if $notinstalled}
                <li class="notinstalled"><b>{str tag='notinstalledplugins'}</b>
                    <ul id="{$plugintype}.notinstalled">
                    {foreach from=$notinstalled key='plugin' item='data'}
                        <li id="{$plugintype}.{$plugin}">{$plugin}
                        {if $data.notinstallable}
                            {str tag='notinstallable'}: {$data.notinstallable}
                        {else}
                            <span id="{$plugintype}.{$plugin}.install">(<a href="" onClick="{$installlink}('{$plugintype}.{$plugin}'); return false;">{str tag='install' section='admin'} <span class="accessible-hidden ">{$plugintype} {$plugin}</span></a>)</span>
                        {/if}
                        <span id="{$plugintype}.{$plugin}.message"></span>
                        </li>
                    {/foreach}
                    </ul>
                </li>
                {/if}
            
                <li><b>{str tag='installedplugins'}</b>
                    <ul id="{$plugintype}.installed">
                    {foreach from=$installed key='plugin' item='data'}
                        <li id="{$plugintype}.{$plugin}">{$plugin}
                        {if $data.activateform}
                            [ {$data.activateform|safe}
                        {/if}
                        {if $data.config}
                            {if !$data.activateform} [ {else} | {/if}
                            <a href="pluginconfig.php?plugintype={$plugintype}&amp;pluginname={$plugin}">{str tag='config'} <span class="accessible-hidden ">{str tag='configfor'} {$plugintype} {$plugin}</span></a>
                        {/if}
                        {if $data.config || $data.activateform} ] {/if}
                        </li>
                        {if $data.types}
                        <li>
                            <ul>
                            {foreach from=$data.types key='type' item='config'}
                                <li>{$type}
                                {if $config} [ <a href="pluginconfig.php?plugintype={$plugintype}&amp;pluginname={$plugin}&amp;type={$type}">{str tag='config'} <span class="accessible-hidden ">{str tag='configfor'} {$plugintype} {$plugin}</span></a> ]{/if}</li>
                            {/foreach}
                            </ul>
                        </li>
                        {/if}
                    {/foreach}
                    </ul>
                </li>
            </ul>
        </li>
    {/foreach}
    </ul>
    <div class="cb"></div>
</div>

{include file='footer.tpl'}
