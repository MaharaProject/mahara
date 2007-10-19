{include file='header.tpl' nosearch='true'}

{include file="columnfullstart.tpl"}
	
            <h2 class="center">{str tag=performinginstallsandupgrades section=admin}</h2>
			<table cellspacing="0" cellpadding="1" id="installer">
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
                    <tr class="{cycle name=rows values=r1,r0}">
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
			<div id="finished" style="visibility: hidden; margin-top: 1em; text-align: center;">{str section=admin tag=successfullyinstalled} <a href="{$WWWROOT}">{str section=admin tag=continue}</a></div>

{include file="columnfullend.tpl"}

{include file='admin/upgradefooter.tpl'}
