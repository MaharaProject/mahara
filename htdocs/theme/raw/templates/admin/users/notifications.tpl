{include file="header.tpl"}

            <table id="notificationstable" class="fullwidth table">
			<thead>
                <tr>
                    <th>&nbsp;</th>
                    <th></th>
                    <th>{str tag='institution'}</th>
{foreach from=$types item='type'}
                    <th>{$type}</th>
{/foreach}
                </tr>
			</thead>
			<tbody>	
{foreach from=$users item='user' key='userid'}
                <tr class="{cycle values="r0,r1"}">
                    <td class='center'><img src="{$WWWROOT}thumb.php?type=profileicon&maxwidth=40&maxheight=40&id={$userid}" alt="profile picture"/></td>
                    <td>{$user.user|display_name}</td>
                    <td>
                    {foreach from=$user.user->institutions item=i}
                        <div>{$i}</div>
                    {/foreach}
                    </td>
{foreach from=$types key='type' item='name'}
                    <td>{if $user.methods.$type}{$user.methods.$type}{else}{str tag='none'}{/if}</td> 
{/foreach} 
                </tr>
{/foreach} 
			</tbody>
            </table>

{include file="footer.tpl"}
