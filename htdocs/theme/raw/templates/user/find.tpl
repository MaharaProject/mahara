{include file="header.tpl"}
            <div id="friendslistcontainer">
            {$form}
{if $users}
            <table id="friendslist" class="fullwidth listing">
                <tbody>
{foreach from=$users item=user}
                    <tr class="r{cycle values=1,0}">
{include file="user/user.tpl" user=$user page='find'}
                    </tr>
{/foreach}
                </tbody>
            </table>
            </div>
{$pagination}
{elseif $message}
            <div class="message">{$message}</div>
{/if}
</div>
{include file="footer.tpl"}
