{include file="header.tpl"}

{if $changepassword}
  {if $changeusername}
            <h1>{str tag="chooseusernamepassword"}</h1>
            <p>{str tag="chooseusernamepasswordinfo" arg1=$sitename}</p>
  {else}
            <h1>{str tag="changepassword"}</h1>
            <p>{str tag="changepasswordinfo"}</p>
  {/if}
            {if $loginasoverridepasswordchange}<div class="message">{$loginasoverridepasswordchange|safe}</div>{/if}
{else}
			<h1>{str tag='requiredfields' section='auth'}</h1>
{/if}

			{$form|safe}

{include file="footer.tpl"}

