{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}

<div id="friendslistcontainer">
    {$form}
{if $users}
    <table id="friendslist" class="fullwidth">
    {foreach from=$users item=user}
        <tr class="r{cycle values=1,0}">
        {include file="user/user.tpl" user=$user page='myfriends'}
        </tr>
    {/foreach}
    </table>
	<div class="center">
	{$pagination}
	</div>
	{else}
	<div class="message">
		{$message}
	</div>
{/if}
</div>
{include file="columnleftend.tpl"}

{include file="footer.tpl"}
