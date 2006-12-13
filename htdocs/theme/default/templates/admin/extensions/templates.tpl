{include file='header.tpl'}
<div id="column-left-full">
	<div class="content">
		<div class="box-cnrs"><span class="cnr-tl"><span class="cnr-tr"><span class="cnr-bl"><span class="cnr-br">
			<div class="maincontent">
<h2>{str tag='templatesadmin' section='admin'}</h2>
<table id="admintemplates">
<tr>
    <th>{str tag='name'}</th>
    <th>{str tag='installed' section='admin'}</th>
    <th>{str tag='errors' section='admin'}</th>
</tr>

{foreach from=$templates item='template' key='name'}
<tr>
    <td>{$name}</td>
    <td>{if $template.installed}
        <img id="{$name}.status" alt="{str tag='yes'}" src="{image_path imagelocation='success.gif}" />
	{if !$template.error}
            <a href="" onClick="{$installlink}('{$name}'); return false;">{str tag='reinstall' section='admin'}</a> 
            <span id="{$name}.message"></span>
 	{/if}
        {else}
        <img id="{$name}.status" alt="{str tag='yes'}" src="{image_path imagelocation='failure.gif}" />
             {if !$template.installed && !$template.error}
                 <a href="" onClick="{$installlink}('{$name}'); return false;">{str tag='install' section='admin'}</a> 
                 <span id="{$name}.message"></span>
             {/if}
        {/if}
    </td>
    <td>{if $template.error} {$template.error} {/if}</td>
</tr>
{/foreach}
</table>
 	</div>
</div>

{include file='footer.tpl'}
