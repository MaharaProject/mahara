{include file="header.tpl"}

{include file="columnfullstart.tpl"}

			{if $forgotpass_form}
			<h2>{str tag='forgotpassword'}</h2>
	
			<p>{str tag="forgotpasswordtext"}</p>
	
			{$forgotpass_form}
			{/if}
			
			{if $forgotpasschange_form}
			<h2>{str tag='changepassword'}</h2>
	
			<p>{str tag="forgotpasswordenternew"}</p>
			
			{$forgotpasschange_form}
			{/if}
{include file="columnfullend.tpl"}

{include file="footer.tpl"}
