{include file="header.tpl"}

            {dynamic}{insert_messages placement='loginbox'}{/dynamic}
            <div id="loginform_container"><noscript><p>{str tag="javascriptnotenabled"}</p></noscript>
            {$login_form|safe}
            </div>

{include file="footer.tpl"}
