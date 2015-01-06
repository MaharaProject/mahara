{include file='header.tpl' nosearch='true'}
            <h2 class="center">{$upgradeheading}</h2>
            <div class="center js-hidden" id="jsrequiredforupgrade">{str tag=jsrequiredforupgrade section=admin}</div>
            <table id="installer" class="nojs-hidden-table">
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
            <div id="finished" class="nojs-hidden-block">{str section=admin tag=successfullyinstalled} <a href="{$WWWROOT}admin/upgrade.php?finished=1">{str section=admin tag=continue}</a></div>

{include file='admin/upgradefooter.tpl'}
