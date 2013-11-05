{include file="header.tpl"}

            <div id="loginform_container"><noscript><p>{str tag="javascriptnotenabled"}</p></noscript>
            {$login_form|safe}
            {dynamic}{insert_messages placement='loginbox'}{/dynamic}
            </div>

{include file="footer.tpl"}
