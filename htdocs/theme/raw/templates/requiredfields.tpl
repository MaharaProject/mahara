{auto_escape off}
{include file="header.tpl"}

{if $changepassword}
  {if $changeusername}
            <h1>{str tag="chooseusernamepassword"}</h1>
            <p>{str tag="chooseusernamepasswordinfo" arg1=$sitename|escape}</p>
  {else}
            <h1>{str tag="changepassword"}</h1>
            <p>{str tag="changepasswordinfo"}</p>
  {/if}
            {if $loginasoverridepasswordchange}<div class="message">{$loginasoverridepasswordchange}</div>{/if}
{else}
			<h1>{str tag='requiredfields' section='auth'}</h1>
{/if}

			{$form}

{include file="footer.tpl"}
{/auto_escape}
