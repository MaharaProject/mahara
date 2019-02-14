{include file='header.tpl' nosearch='true'}

<div class="view-container">
    <h1>
        {$upgradeheading}
    </h1>
    <div class="js-hidden alert alert-danger" id="jsrequiredforupgrade">
        {str tag=jsrequiredforupgrade section=admin}
    </div>
    <div class="card">
        <table class="table upgrade-list">
            <thead>
                <tr>
                    <th>{str section=admin tag=component}</th>
                    <th>{str section=admin tag=fromversion}</th>
                    <th>{str section=admin tag=toversion}</th>
                    <th id="msgscol">{str section=admin tag=Information}</th>
                </tr>
            </thead>
            <tbody>
            {foreach from=$upgrades key=name item=upgrade}
                <tr>
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
        <div id="installdone" class="d-none nojs-hidden-block card-body">
            {str section=admin tag=successfullyinstalled}
            <a href="{$WWWROOT}admin/upgrade.php?finished=1">
                {str section=admin tag=continue}
            </a>
        </div>
    </div>
</div>
{include file='admin/upgradefooter.tpl'}
