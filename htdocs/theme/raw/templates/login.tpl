{include file="header.tpl"}

{include file="columnfullstart.tpl"}
			<h2>{$loginmessage|escape}</h2>
			
            <div id="loginform_container"><noscript><p>{str tag="javascriptnotenabled"}</p></noscript>
            {$login_form}
            </div>
				
{include file="columnfullend.tpl"}

{include file="footer.tpl"}
