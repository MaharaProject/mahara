{include file="header.tpl"}

<div id="column-full">
	<div class="maincontent">
	<h2>{str tag="changepassword"}</h2>
			
	<p>{str tag="changepasswordinfo"}</p>

    {if $loginasoverridepasswordchange}<div class="message">{$loginasoverridepasswordchange}</div>{/if}

	{$change_password_form}
	</div>
</div>

{include file="footer.tpl"}
