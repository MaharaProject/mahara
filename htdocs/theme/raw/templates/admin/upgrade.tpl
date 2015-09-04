{include file='header.tpl' nosearch='true'}

<h1 class="mtxl">{$upgradeheading}</h1>
<div class="center js-hidden alert alert-danger" id="jsrequiredforupgrade">{str tag=jsrequiredforupgrade section=admin}</div>
<div class="panel panel-default">
    <table id="" class="table">
        <thead>
            <tr>
                <th>{str section=admin tag=component}</th>
                <th>{str section=admin tag=fromversion}</th>
                <th>{str section=admin tag=toversion}</th>
                <th id="msgscol">{str section=admin tag=information}</th>
            </tr>
        </thead>
        <tbody>
        {foreach from=$upgrades key=name item=upgrade}
            <tr class="{cycle name=rows values='r0,r1'}">
                <td>{$name}</td>
                {if $name == 'firstcoredata' || $name == 'lastcoredata'}
                <td></td>
                <td></td>
                {else}
                <td>{if $upgrade->install} {str section='admin' tag='notinstalled'} {else} {$upgrade->fromrelease} {/if} </td>
                <td>{$upgrade->torelease}</td>
                {/if}
                <td id="{$name}" class="msgscol">&nbsp;</td>
            </tr>
        {/foreach}
        </tbody>
    </table>
    <div id="installdone" class="hidden nojs-hidden-block panel-body">{str section=admin tag=successfullyinstalled} <a href="{$WWWROOT}admin/upgrade.php?finished=1">{str section=admin tag=continue}</a></div>
</div>
{include file='admin/upgradefooter.tpl'}
