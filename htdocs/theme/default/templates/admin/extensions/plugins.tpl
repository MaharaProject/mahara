{include file='header.tpl'}

{include file="columnfullstart.tpl"}

<h2>{str tag='pluginadmin' section='admin'}</h2>
<div id="adminplugin">
<ul class="adminpluginstypes">
{foreach from=$plugins key='plugintype' item='plugins'}
    <li><h4>{str tag='plugintype'}: {$plugintype}</h4></li>
    {assign var="installed" value=$plugins.installed}
    {assign var="notinstalled" value=$plugins.notinstalled} 
    <ul>
        <li><b>{str tag='installedplugins'}</b></li>
        <ul id="{$plugintype}.installed">
    {foreach from=$installed key='plugin' item='data'}
	<li id="{$plugintype}.{$plugin}">{$plugin}
        {if $data.config}
            (<a href="pluginconfig.php?plugintype={$plugintype}&amp;pluginname={$plugin}">{str tag='config'}</a>)
        {/if}</li>
        {if $data.types} 
	    <ul>
	    {foreach from=$data.types key='type' item='config'}
		<li>{$type} 
                {if $config} (<a href="pluginconfig.php?plugintype={$plugintype}&amp;pluginname={$plugin}&amp;type={$type}">{str tag='config'}</a>){/if}</li>
	    {/foreach}
	    </ul>
	{/if}
    {/foreach}
        </ul>
    {if $notinstalled} 
        <li><b>{str tag='notinstalledplugins'}</b></li>
        <ul id="{$plugintype}.notinstalled">
        {foreach from=$notinstalled key='plugin' item='data'}
	    <li id="{$plugintype}.{$plugin}">{$plugin} {if $data.notinstallable} {str tag='notinstallable'} {$data.notinstallable} 
                      {else} <span id="{$plugintype}.{$plugin}.install">(<a href="" onClick="{$installlink}('{$plugintype}.{$plugin}'); return false;">{str tag='install' section='admin'}</a>)</span>
	              {/if}
            <span id="{$plugintype}.{$plugin}.message"></span>
            </li>
	{/foreach}
        </ul>
    {/if}
    </ul>
{/foreach}
</ul>
</div>
{include file="columnfullend.tpl"}

{include file='footer.tpl'}
