{include file="header.tpl"}

{include file="columnfullstart.tpl"}
            <table id="notificationstable">
                <tr>
                    <th>&nbsp;</th>
                    <th></th>
                    <th>{str tag='institution'}</th>
{foreach from=$types item='type'}
                    <th>{$type}</th>
{/foreach}
                </tr>
{foreach from=$users item='user' key='userid'}
                <tr class="{cycle values="r0,r1"}">
                    <td><img src="{$WWWROOT}thumb.php?type=profileicon&size=40x40&id={$userid}" alt="profile icon"/></td>
                    <td>{display_name user=$user.user}</td>
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

            </table>
{include file="columnfullend.tpl"}

{include file="footer.tpl"}
