{include file="header.tpl"}
<div class="panel panel-default">
    <table id="notificationstable" class="fullwidth table">
	   <thead>
            <tr>
                <th></th>
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
                    <td class='center'><img src="{profile_icon_url user=$user.user maxheight=40 maxwidth=40}" alt="{str tag=profileimagetext arg1=$user.user|display_default_name}"/></td>
                    <td>{$user.user|display_name}</td>
                    <td>
                    {foreach from=$user.user->institutions item=i}
                        <div>{$i}</div>
                    {/foreach}
                    </td>
{foreach from=$types key='type' item='name'}
                    <td class="center">{if $user.methods.$type}{$user.methods.$type}{else}{str tag='none'}{/if}</td>
{/foreach} 
                </tr>
{/foreach} 
			</tbody>
    </table>
</div>
{include file="footer.tpl"}
