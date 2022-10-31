{include file='header.tpl'}

<div id="register-site-full">
    <div class="card col-lg-9">
        <div class="card-body">
        {if $registrationupdate}
            <div class="card bg-danger view-container">
                <h2 class="card-header">{str tag=siteregistrationpolicy1 section=admin}</h2>
                <div class="card-body">{$registrationupdate|safe}</div>
            </div>
        {/if}
        {str tag=registeryourmaharasitedetail1 section=admin}
        {if $firstregistered}
            <p><strong>{str tag=siteisregisteredsince1 section=admin args=$firstregistered}</strong></p>
        {elseif $isregistered}
            <p><strong>{str tag=siteisregistered1 section=admin}</strong></p>
        {else}
            <p><strong>{str tag=sitenotregistered section=admin}</strong></p>
        {/if}
        {$registerinfo|safe}
        </div>
    </div>
</div>
{include file='footer.tpl'}
