<div id="verifyform" class="toolbarhtml">
    {if $showsignoff}
    <div>
        <div class="signoff-title">
            {str tag=signedoff section=blocktype.peerassessment/signoff}
        </div>
        {if $signable}
        <a href="#" id="signoff">
            {$signoffbutton|safe}
            <span class="visually-hidden">{str tag=updatesignoff section=blocktype.peerassessment/signoff}</span>
        </a>
            {if !$signoff}
            <div class="progress-help text-small">{str tag=signoffhelppage section=blocktype.peerassessment/signoff}</div>
            {/if}
        {elseif $signoff}
            {$signoffbutton|safe}
        {else}
        <span class="icon icon-circle dot disabled icon-lg"></span>
        {/if}
    </div>
    {/if}
    {if $showverify}
    <div class="clearright">
        <div class="verified-title">
            {str tag=verified section=blocktype.peerassessment/signoff}
        </div>
        {if $verified}
            {$verifybutton|safe}
        {elseif $verifiable && $signoff}
        <a href="#" id="verify">
            {$verifybutton|safe}
            <span class="visually-hidden">{str tag=updateverify section=blocktype.peerassessment/signoff}</span>
        </a>
        {else}
        <span class="icon icon-circle dot disabled icon-lg"></span>
        {/if}
    </div>
    {/if}

    <div class="help">
        <a href="#" id="signoff-info-icon" class="hidden" title="{str tag=viewsignoffdetails section=blocktype.peerassessment/signoff}">
            <span class="icon icon-info-circle"></span>
            <span class="visually-hidden">{str tag=viewsignoffdetails section=blocktype.peerassessment/signoff}</span>
        </a>
    </div>
</div>

{* signoff modal form *}
    <div tabindex="0" class="modal fade" id="signoff-confirm-form">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{str tag=Close}"><span aria-hidden="true">&times;</span></button>
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
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{str tag=Close}"><span aria-hidden="true">&times;</span></button>
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
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{str tag=Close}"><span aria-hidden="true">&times;</span></button>
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
                    $('#dummyform_signoff').prop('checked', true);
                    $('.progress-help').addClass('hidden');
                    $('#signoff-info-icon').removeClass('hidden');
                    signedoff = '1';
                }
                else {
                    $('#dummyform_signoff').prop('checked', false);
                    $('.progress-help').removeClass('hidden');
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
                    $('#dummyform_verify').prop('checked', true);
                    $('#dummyform_verify').prop('disabled', true);
                    $('#verify').off('click');
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
