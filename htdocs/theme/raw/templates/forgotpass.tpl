{auto_escape off}
{include file="header.tpl"}
{if $forgotpass_form}
            {str tag="forgotusernamepasswordtext"}

            {$forgotpass_form}
{/if}
{if $forgotpasschange_form}
            <p>{str tag="forgotpasswordenternew"}</p>

            {$forgotpasschange_form}
{/if}
{include file="footer.tpl"}
{/auto_escape}
