<div id="verifyform" class="toolbarhtml">
    <div>
        {str tag=signoff section=blocktype.peerassessment/peerassessment}
        {if $signable}
        <a href="#" id="signoff">
            <span class="icon {if $signoff}icon-check-circle completed {else}icon-circle incomplete{/if} icon-lg"></span>
        </a>
        {elseif $signoff}
        <span class="icon icon-check-circle completed icon-lg"></span>
        {else}
        <span class="icon icon-circle dot disabled icon-lg"></span>
        {/if}
    </div>
    <div>
        {str tag=verify section=blocktype.peerassessment/peerassessment}
        {if $verifiable && $signoff}
        <a href="#" id="verify">
            <span class="icon {if $verified}icon-check-circle completed {else}icon-circle incomplete{/if} icon-lg"></span>
        </a>
        {elseif $verified}
        <span class="icon icon-check-circle completed icon-lg"></span>
        {else}
        <span class="icon icon-circle dot disabled icon-lg"></span>
        {/if}
    </div>
</div>

<script type="application/javascript">
$(function() {
    $('#signoff').on('click', function(event) {
        event.preventDefault();
        event.stopPropagation();
        sendjsonrequest('{$WWWROOT}artefact/peerassessment/completion.json.php', { 'view': '{$view}', 'signoff': 1 }, 'POST', function (data) {
            if (data.data) {
                if (data.data.signoff_newstate) {
                    $('#signoff span.icon').addClass('icon-check-circle completed').removeClass('icon-circle incomplete');
                }
                else {
                    $('#signoff span.icon').addClass('icon-circle incomplete').removeClass('icon-check-circle completed');
                }
                if (data.data.verify_change) {
                    $('#signoff').parent().next().find('span.icon').addClass('icon-circle dot disabled').removeClass('icon-check-circle completed');
                }
            }
        });
    });
    $('#verify').on('click', function(event) {
        event.preventDefault();
        event.stopPropagation();
        sendjsonrequest('{$WWWROOT}artefact/peerassessment/completion.json.php', { 'view': '{$view}', 'verify': 1 }, 'POST', function (data) {
            if (data.data) {
                if (data.data.verify_newstate) {
                    $('#verify span.icon').addClass('icon-check-circle completed').removeClass('icon-circle incomplete');
                }
            }
        });
    });
});
</script>