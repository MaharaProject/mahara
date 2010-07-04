{include file="header.tpl"}
{if $forgotpass_form}
            {str tag="forgotusernamepasswordtext"}

            {$forgotpass_form|safe}
{/if}
{if $forgotpasschange_form}
            <p>{str tag="forgotpasswordenternew"}</p>

            {$forgotpasschange_form|safe}
{/if}
{include file="footer.tpl"}
