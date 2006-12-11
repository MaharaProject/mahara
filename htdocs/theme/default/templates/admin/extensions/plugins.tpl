{include file='header.tpl'}

<div id="column-left-full">
	<div class="content">
		<div class="box-cnrs"><span class="cnr-tl"><span class="cnr-tr"><span class="cnr-bl"><span class="cnr-br">
			<div class="maincontent">
<h2>{str tag='pluginadmin' section='admin'}</h2>
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
                      {else} (<a href="" onClick="{$installlink}('{$plugintype}.{$plugin}'); return false;">install</a>)
	              {/if}
            <div id="{$plugintype}.{$plugin}.message"></div>
            </li>
	{/foreach}
        </ul>
    {/if}
    </ul>
{/foreach}
</ul>
	</div>
</div>

{include file='footer.tpl'}
