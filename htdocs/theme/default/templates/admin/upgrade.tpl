{include file='header.tpl'}
{if $upgrades}
<table>
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
</table>
<div id="finished" style="display: none;">All done! <a href="{$WWWROOT}">Continue</a> (FIXME: displays on errors)</div>
{else}
{str section=admin tag=noupgrades}
{/if}
{include file='footer.tpl'}
