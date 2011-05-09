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
        <li><b>{str tag='installedplugins'}</b>
        <ul id="{$plugintype}.installed">
    {foreach from=$installed key='plugin' item='data'}
	<li id="{$plugintype}.{$plugin}">{$plugin}
        {if $data.disableable}
            [ {$data.activateform|safe}
        {/if}
        {if $data.config}
            {if !$data.disableable} [ {else} | {/if}
            <a href="pluginconfig.php?plugintype={$plugintype}&amp;pluginname={$plugin}">{str tag='config'}</a>
        {/if} {if $data.config || $data.disableable} ] {/if} </li>
        {if $data.types} 
	    <li><ul>
	    {foreach from=$data.types key='type' item='config'}
		<li>{$type} 
                {if $config} [ <a href="pluginconfig.php?plugintype={$plugintype}&amp;pluginname={$plugin}&amp;type={$type}">{str tag='config'}</a> ]{/if}</li>
	    {/foreach}
	    </ul></li>
	{/if}
    {/foreach}
        </ul></li>
    {if $notinstalled} 
        <li><b>{str tag='notinstalledplugins'}</b>
        <ul id="{$plugintype}.notinstalled">
        {foreach from=$notinstalled key='plugin' item='data'}
	    <li id="{$plugintype}.{$plugin}">{$plugin} {if $data.notinstallable} {str tag='notinstallable'} {$data.notinstallable} 
                      {else} <span id="{$plugintype}.{$plugin}.install">(<a href="" onClick="{$installlink}('{$plugintype}.{$plugin}'); return false;">{str tag='install' section='admin'}</a>)</span>
	              {/if}
            <span id="{$plugintype}.{$plugin}.message"></span>
            </li>
	{/foreach}</li>
        </ul>
    {/if}
    </ul></li>
{/foreach}
</ul>
<div class="cb"></div>
</div>

{include file='footer.tpl'}
