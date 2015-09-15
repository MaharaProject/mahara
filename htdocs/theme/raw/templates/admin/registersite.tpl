{include file='header.tpl'}

<div id="register-site-full">
    <h1> {if isset($PAGEICON)}
        <span class="{$PAGEICON}"></span>
        {/if}
        {str tag=registeryourmaharasite section=admin}
    </h1>
    <div class="panel panel-default col-md-9">
        <div class="panel-body">
        {if get_config('new_registration_policy')}
            {str tag=newsiteregistrationpolicy section=admin}
        {/if}
        {if $register}
            {str tag=registeryourmaharasitedetail section=admin args=$WWWROOT}
            {$register|safe}
        {else}
            {str tag=siteregistered section=admin args=$WWWROOT}
        {/if}
        </div>
    </div>
</div>
{include file='footer.tpl'}
