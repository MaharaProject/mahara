{include file='header.tpl'}
{include file="columnfullstart.tpl"}

<h2>{str tag='templatesadmin' section='admin'}</h2>
<table id="admintemplates">
<tr>
    <th>{str tag='name'}</th>
    <th>{str tag='installed' section='admin'}</th>
    <th>{str tag='errors' section='admin'}</th>
</tr>

{foreach from=$templates item='template' key='name'}
<tr class="{cycle values=r1,r0}">
    <td>{$name}</td>
    <td>{if $template.installed}
        <img id="{$name}.status" alt="{str tag='yes'}" src="{theme_path location='success.gif}" />
	{if !$template.error}
            <a href="" onClick="{$installlink}('{$name}'); return false;" id="admintemplates_reinstall">{str tag='reinstall' section='admin'}</a> 
            <span id="{$name}.message"></span>
 	{/if}
        {else}
        <img id="{$name}.status" alt="{str tag='yes'}" src="{theme_path location='failure.gif}" />
             {if !$template.installed && !$template.error}
                 <a href="" onClick="{$installlink}('{$name}'); return false;" id="admintemplates_install">{str tag='install' section='admin'}</a> 
                 <span id="{$name}.message"></span>
             {/if}
        {/if}
    </td>
    <td>{if $template.error} {$template.error} {/if}</td>
</tr>
{/foreach}
</table>


{include file="columnfullend.tpl"}

{include file='footer.tpl'}
