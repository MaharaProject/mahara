{include file="header.tpl"}

{if $messages}
<table id="messagethread" class="fullwidth listing">
    <tbody>
    {foreach from=$messages item=message}
        <tr class="{cycle values='r0,r1'}">
        {if $message->usr == $user->id}
            <th>{include file="user/simpleuser.tpl" user=$USER}</th>
        {else}
            <th>{include file="user/simpleuser.tpl" user=$user}</th>
        {/if}
            <td>{$message->message}</td>
        </tr>
    {/foreach}
    </tbody>
</table>
{/if}

{$form}

{include file="footer.tpl"}
