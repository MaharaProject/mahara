{include file="header.tpl"}

<div id="column-full">
	<div class="maincontent">
	<h2>{str tag="changepassword"}</h2>
			
	<p>{str tag="changepasswordinfo"}</p>

    <div class="message">{$loginasoverridepasswordchange}</div>

	{$change_password_form}
	</div>
</div>

{include file="footer.tpl"}
