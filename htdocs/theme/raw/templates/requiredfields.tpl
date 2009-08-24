{include file="header.tpl"}

{if $changepassword}
            <h1>{str tag="changepassword"}</h1>
            <p>{str tag="changepasswordinfo"}</p>
            {if $loginasoverridepasswordchange}<div class="message">{$loginasoverridepasswordchange}</div>{/if}
{else}
			<h1>{str tag='requiredfields' section='auth'}</h1>
{/if}

			{$form}

{include file="footer.tpl"}
