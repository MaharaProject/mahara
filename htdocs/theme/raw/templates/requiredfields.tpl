{include file="header.tpl"}

{if $changepassword}
    {if $changeusername}
    
    <h1>{str tag="chooseusernamepassword"}</h1>
    <p class="lead">{str tag="chooseusernamepasswordinfo" arg1=$sitename}</p>
    
    {else}
    
    <h1>{str tag="changepassword"}</h1>
    <p class="lead">{str tag="changepasswordinfo"}</p>
    
    {/if}
    
    {if $loginasoverridepasswordchange}
    <p class="lead">
        {$loginasoverridepasswordchange|safe}
    </p>
    {/if}

{else}
    <h1>{str tag='requiredfields' section='auth'}</h1>
{/if}

{$form|safe}

{include file="footer.tpl"}

