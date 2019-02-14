{include file='header.tpl'}

<div id="register-site-full">
    <div class="card col-lg-9">
        <div class="card-body">
        {if get_config('new_registration_policy')}
            {str tag=siteregistrationpolicy section=admin}
        {/if}
        {if $register}
            {str tag=registeryourmaharasitedetail section=admin}
            {$register|safe}
        {else}
            {if $firstregistered}
                <p><strong>{str tag=siteisregisteredsince section=admin args=$firstregistered}</strong></p>
            {else}
                <p><strong>{str tag=siteisregistered section=admin}</strong></p>
            {/if}
            {str tag=registeryourmaharasitedetail section=admin}
            {$registered|safe}
        {/if}
        </div>
    </div>
</div>
{include file='footer.tpl'}
