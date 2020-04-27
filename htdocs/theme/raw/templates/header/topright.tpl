<ul class="nav topright-menu">
    {if !$nosearch && !$LOGGEDIN && $languageform}
        <li id="language" class="language-form">
            {$languageform|safe}
        </li>
    {/if}
    {if !$LOGGEDIN && !$SHOWLOGINBLOCK && !$LOGINPAGE}
        <li id="loginlink" class="has-icon login-link">
            <a href="{$WWWROOT}?login">
                <span class="icon icon-sign-in-alt" role="presentation" aria-hidden="true"></span>
                <span>{str tag="login"}</span>
            </a>
        </li>
    {/if}
</ul>
