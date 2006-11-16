{include file='header.tpl'}
<table cellspacing="0" cellpadding="1" id="installer">
    <tr>
        <th>{str section=admin tag=component}</th>
        <th>{str section=admin tag=fromversion}</th>
        <th>{str section=admin tag=toversion}</th>
        <th></th>
    </tr>
{foreach from=$upgrades key=name item=upgrade}
    <tr>
        <td>{$name}</td>
        <td>{if $upgrade->install} {str section='admin' tag='notinstalled'} {else} {$upgrade->fromrelease} {/if} </td>
        <td>{$upgrade->torelease}</td>
        <td id="{$name}">&nbsp;</td>
    </tr>
{/foreach}
{if $install}
    <tr>
        <td>{str section=admin tag=coredata}</td>
        <td></td>
        <td></td>
        <td id="coredata">&nbsp;</td>
    </tr>
{/if}
</table>
<div id="finished" style="visibility: hidden;">{str section=admin tag=successfullyinstalled} <a href="{$WWWROOT}">{str section=admin tag=continue}</a></div>
{include file='admin/upgradefooter.tpl'}
