{include file='header.tpl' nosearch='true'}

<div id="column-full">
	<div class="content">
		<div class="box-cnrs"><span class="cnr-tl"><span class="cnr-tr"><span class="cnr-bl"><span class="cnr-br">
			<div class="maincontent">
	
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
                    <tr>
                        <td>{$name}</td>
                        <td>{if $upgrade->install} {str section='admin' tag='notinstalled'} {else} {$upgrade->fromrelease} {/if} </td>
                        <td>{$upgrade->torelease}</td>
                        <td id="{$name}" class="msgscol">&nbsp;</td>
                    </tr>
                {/foreach}
                {if $install}
                    <tr>
                        <td>{str section=admin tag=coredata}</td>
                        <td></td>
                        <td></td>
                        <td id="coredata" class="msgscol">&nbsp;</td>
                    </tr>
                {/if}
                </tbody>
			</table>
			<div id="finished" style="visibility: hidden; margin-top: 1em; text-align: center;">{str section=admin tag=successfullyinstalled} <a href="{$WWWROOT}">{str section=admin tag=continue}</a></div>

			</div>
		</span></span></span></span></div>	
	</div>
</div>

{include file='admin/upgradefooter.tpl'}
