{include file='header.tpl'}

<h2>Administration</h2>

{foreach from=$plugins key='plugintype' item='plugins'}
    <h4>{$plugintype}</h4>
    {assign var="installed" value=$plugins.installed}
    {assign var="notinstalled" value=$plugins.notinstalled}
    {foreach from=$installed key='plugin' item='data'}
	{$plugin}
        {if $data.config}
            (<a href="pluginconfig.php?plugintype={$plugintype}&amp;pluginname={$plugin}">{str tag='config'}</a>
        {/if}<br />
        {if $data.types} 
	    {foreach from=$data.types key='type' item='config'}
		&nbsp;&nbsp;&nbsp;{$type} 
                {if $config} (<a href="pluginconfig.php?plugintype={$plugintype}&amp;pluginname={$plugin}&amp;type={$type}">{str tag='config'}</a>){/if}<br />
	    {/foreach}
	{/if}
    {/foreach}
    {foreach from=$notinstalled item='plugin}
	{$plugin}<br />
    {/foreach}
{/foreach}

{include file='footer.tpl'}
