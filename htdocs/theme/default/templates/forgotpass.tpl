{include file="header.tpl"}

<h2>ForgotPass</h2>

{if $forgotpass_form}
<p>You forgot your password! Enter your e-mail address here and we will send you a key you can use to give yourself a new password.</p>

{$forgotpass_form}
{/if}

{if $forgotpasschange_form}
<p>Please enter your new password to continue</p>

{$forgotpasschange_form}
{/if}

{include file="footer.tpl"}
