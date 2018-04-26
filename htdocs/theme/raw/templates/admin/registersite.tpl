{include file='header.tpl'}

<div id="register-site-full">
    <div class="panel panel-default col-md-9">
        <div class="panel-body">
        {if get_config('new_registration_policy')}
            {str tag=siteregistrationpolicy section=admin}
        {/if}
        {if $register}
            {str tag=registeryourmaharasitedetail section=admin args=$WWWROOT}
            {$register|safe}
        {else}
            {if $firstregistered}
                <p><strong>{str tag=siteisregisteredsince section=admin args=$firstregistered}</strong></p>
                {str tag=registeredinfo section=admin}
                {$registered|safe}
            {else}
                <p><strong>{str tag=siteisregistered section=admin}</strong></p>
                {str tag=registeredinfo section=admin}
                {$registered|safe}
            {/if}
        {/if}
        </div>
    </div>
</div>
{include file='footer.tpl'}
