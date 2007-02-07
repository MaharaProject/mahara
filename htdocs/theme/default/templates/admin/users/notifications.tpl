{include file="header.tpl"}

{include file="columnfullstart.tpl"}
			<h2>{str tag='adminnotifications' section='admin'}</h2>
            <table>
                <tr>
                    <th>&nbsp;</th>
                    <th>{str tag='profileicon'}</th>
{foreach from=$types item='type'}
                    <th>{$type}</th>
{/foreach}
                </tr>
{foreach from=$users item='user' key='userid'}
                <tr>
                    <td>{display_name user=$user.user}</td>
                    <td><img src="{$WWWROOT}thumb.php?type=profileicon&size=40x40&id={$userid}" alt="profile icon"/></td>
{foreach from=$types key='type' item='name'}
                    <td>{if $user.methods.$type}{$user.methods.$type}{else}{str tag='none'}{/if}</td> 
{/foreach} 
                </tr>
{/foreach} 

            </table>
{include file="columnfullend.tpl"}

{include file="footer.tpl"}
