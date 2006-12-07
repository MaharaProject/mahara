{include file='header.tpl'}

<div id="column-right">
</div>

<div id="column-left">
<div class="content">
<div class="box-cnrs"><span class="cnr-tl"><span class="cnr-tr"><span 
class="cnr-bl"><span class="cnr-br">
	<div class="maincontent">
	
<h2>Plugin Administration</h2>

{foreach from=$plugins key='plugintype' item='plugins'}
    <h4>{str tag='plugintype'}: {$plugintype}</h4>
    {assign var="installed" value=$plugins.installed}
    {assign var="notinstalled" value=$plugins.notinstalled} 
    <p><b>{str tag='installedplugins'}</b></p>
    {foreach from=$installed key='plugin' item='data'}
	{$plugin}
        {if $data.config}
            (<a href="pluginconfig.php?plugintype={$plugintype}&amp;pluginname={$plugin}">{str tag='config'}</a>)
        {/if}<br />
        {if $data.types} 
	    {foreach from=$data.types key='type' item='config'}
		&nbsp;&nbsp;&nbsp;{$type} 
                {if $config} (<a href="pluginconfig.php?plugintype={$plugintype}&amp;pluginname={$plugin}&amp;type={$type}">{str tag='config'}</a>){/if}<br />
	    {/foreach}
	{/if}
    {/foreach}
    {if $notinstalled} 
        <p><b>{str tag='notinstalledplugins'}</b></p>
        {foreach from=$notinstalled key='plugin' item='data'}
	    {$plugin} {if $data.notinstallable} {str tag='notinstallable'} {$data.notinstallable} 
                      {else} (<a href="" onClick="{$installlink}('{$plugintype}.{$plugin}'); return false;">install</a>)
	              {/if}
  	<div id="{$plugintype}.{$plugin}"></div>
	{/foreach}
    {/if}
{/foreach}

	</div>
</span></span></span></span></div>	
</div>
</div>
{include file='footer.tpl'}
