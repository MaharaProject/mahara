{auto_escape off}
{include file="header.tpl"}

	<h1>{str tag="changepassword"}</h1>
			
	<p>{str tag="changepasswordinfo"}</p>

    {if $loginasoverridepasswordchange}<div class="message">{$loginasoverridepasswordchange}</div>{/if}

	{$change_password_form}

{include file="footer.tpl"}
{/auto_escape}
