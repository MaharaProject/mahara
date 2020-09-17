<div id="verifyform" class="toolbarhtml">
    {if $showsignoff}
    <div>
        {str tag=signedoff section=blocktype.peerassessment/signoff}
        {if $signable}
        <a href="#" id="signoff">
            <span class="icon {if $signoff}icon-check-circle completed {else}icon-circle incomplete{/if} icon-lg"></span>
            <span class="sr-only">{str tag=updatesignoff section=blocktype.peerassessment/signoff}</span>
        </a>
        {elseif $signoff}
        <span class="icon icon-check-circle completed icon-lg"></span>
        {else}
        <span class="icon icon-circle dot disabled icon-lg"></span>
        {/if}
    </div>
    {/if}
    {if $showverify}
    <div>
        {str tag=verified section=blocktype.peerassessment/signoff}
        {if $verifiable && $signoff}
        <a href="#" id="verify">
            <span class="icon {if $verified}icon-check-circle completed {else}icon-circle incomplete{/if} icon-lg"></span>
            <span class="sr-only">{str tag=updateverify section=blocktype.peerassessment/signoff}</span>
        </a>
        {elseif $verified}
        <span class="icon icon-check-circle completed icon-lg"></span>
        {else}
        <span class="icon icon-circle dot disabled icon-lg"></span>
        {/if}
    </div>
    {/if}

    <div class="help">
        <a href="#" id="signoff-info-icon" class="hidden" title="{str tag=viewsignoffdetails section=blocktype.peerassessment/signoff}">
            <span class="icon icon-info-circle"></span>
            <span class="sr-only">{str tag=viewsignoffdetails section=blocktype.peerassessment/signoff}</span>
        </a>
    </div>
</div>

{* signoff modal form *}
    <div tabindex="0" class="modal fade" id="signoff-confirm-form">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn close" data-dismiss="modal" aria-label="{str tag=Close}"><span aria-hidden="true">&times;</span></button>
                    <h1 class="modal-title">
                        {str tag=signoffpagetitle section=blocktype.peerassessment/signoff}
                    </h1>
                </div>
                <div class="modal-body">
                    <p id="signoff-on" class="hidden">{str tag=signoffpageundodesc section=blocktype.peerassessment/signoff}</p>
                    <p id="signoff-off" class="hidden">{str tag=signoffpagedesc section=blocktype.peerassessment/signoff}</p>
                    <div class="btn-group">
                        <button id="signoff-yes-button" type="button" class="btn btn-secondary">{str tag="yes"}</button>
                        <button id="signoff-back-button" type="button" class="btn btn-secondary">{str tag="no"}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
{* verify modal form *}
    <div tabindex="0" class="modal fade" id="verify-confirm-form">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn close" data-dismiss="modal" aria-label="{str tag=Close}"><span aria-hidden="true">&times;</span></button>
                    <h1 class="modal-title">
                        {str tag=verifypagetitle section=blocktype.peerassessment/signoff}
                    </h1>
                </div>
                <div class="modal-body">
                    <p>{str tag=verifypagedesc section=blocktype.peerassessment/signoff}</p>
                    <div class="btn-group">
                        <button id="verify-yes-button" type="button" class="btn btn-secondary">{str tag="yes"}</button>
                        <button id="verify-back-button" type="button" class="btn btn-secondary">{str tag="no"}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
{* signoff info modal *}
    <div tabindex="0" class="modal fade" id="signoff-info-modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="{str tag=Close}">
                        <span aria-hidden="true">×</span>
                    </button>
                    <h1 class="modal-title">
                        <span class="icon icon-check-circle left" role="presentation" aria-hidden="true"></span>
                        {str tag=signoffdetails section=blocktype.peerassessment/signoff}
                    </h1>
                </div>
                <div class="modal-body">
                    <p id="signoff-info"></p>
                </div>
            </div>
        </div>
    </div>

<script type="application/javascript">
$(function() {
    $("#signoff-back-button, #verify-back-button").on('click', function() {
        $("#signoff-confirm-form").modal('hide');
        $("#verify-confirm-form").modal('hide');
    });
    var signedoff = '{$signoff}';
    var showverify = '{$showverify}';
    if (signedoff) {
        $('#signoff-info-icon').removeClass('hidden');
    }
    $('#signoff').on('click', function(event) {
        event.preventDefault();
        event.stopPropagation();
        if (signedoff) {
            $('#signoff-on').removeClass('hidden');
            $('#signoff-off').addClass('hidden');
        }
        else {
            $('#signoff-on').addClass('hidden');
            $('#signoff-off').removeClass('hidden');
        }
        $j("#signoff-confirm-form").modal('show');
    });

    $('#signoff-yes-button').on('click', function(event) {
        $("#verify-confirm-form").modal('hide');
        event.preventDefault();
        event.stopPropagation();
        sendjsonrequest('{$WWWROOT}artefact/peerassessment/completion.json.php', { 'view': '{$view}', 'signoff': 1 }, 'POST', function (data) {
            if (data.data) {
                if (data.data.signoff_newstate) {
                    $('#signoff span.icon').addClass('icon-check-circle completed').removeClass('icon-circle incomplete');
                    $('#signoff-info-icon').removeClass('hidden');
                    signedoff = '1';
                }
                else {
                    $('#signoff span.icon').addClass('icon-circle incomplete').removeClass('icon-check-circle completed');
                    signedoff = '';
                    $('#signoff-info-icon').addClass('hidden');
                }
                if (data.data.verify_change && showverify) {
                    $('#signoff').parent().next().find('span.icon').addClass('icon-circle dot disabled').removeClass('icon-check-circle completed');
                }
            }
            $("#signoff-confirm-form").modal('hide');
        });
    });

    $('#verify').on('click', function(event) {
        event.preventDefault();
        event.stopPropagation();
        $('body').prepend($('#verify-confirm-form')); // Move form in DOM so it display ok in IE11
        $("#verify-confirm-form").modal('show');
    });

    $('#verify-yes-button').on('click', function(event) {
        $("#signoff-confirm-form").modal('hide');
        event.preventDefault();
        event.stopPropagation();
        sendjsonrequest('{$WWWROOT}artefact/peerassessment/completion.json.php', { 'view': '{$view}', 'verify': 1 }, 'POST', function (data) {
            if (data.data) {
                if (data.data.verify_newstate) {
                    $('#verify span.icon').addClass('icon-check-circle completed').removeClass('icon-circle incomplete');
                }
            }
            $("#verify-confirm-form").modal('hide');
        });
    });

    $('#signoff-info-icon').on('click', function(event) {
        sendjsonrequest('{$WWWROOT}artefact/peerassessment/completion.json.php', { 'view': '{$view}', 'signoffstatus': 1 }, 'POST', function (data) {
            if (data.data) {
                if (data.data) {
                    $('#signoff-info').html(data.data);
                }
                $('#signoff-info-modal').modal('show');
            }
        });
    });

});
</script>
