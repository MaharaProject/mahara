{if !$contentvisible && !$titlevisible}
      {* show nothing *}
{else}
<div id="verification-{$blockid}" class="verification-statement{if $data.primary} primary-statement{/if}">
    {if $title && $titlevisible}
        <h2>{$title}</h2>
    {/if}
    {if $availabilitydatemessage}<div class="alert alert-info">{$availabilitydatemessage}</div>{/if}
    {if $data.text}
        {if $data.addcomment}
            {* no verify button *}
        {elseif $isverified}
            {if $canunverify && !$inedit}
            <a href="#" id="verify-{$blockid}">
            {/if}
                <span class="verificationicon icon icon-check-square float-end"></span>
                <span class="visually-hidden">{str tag='verifiedspecific' section='blocktype.verification' arg1=$title}</span>
            {if $canunverify && !$inedit}
            </a>
            {/if}
        {elseif $canverify}
            {if !$inedit}
            <a href="#" id="verify-{$blockid}">
            {/if}
                <span class="verificationicon icon icon-square icon-regular float-end"></span>
                <span class="visually-hidden">{str tag='toverifyspecific' section='blocktype.verification' arg1=$title}</span>
            {if !$inedit}
            </a>
            {/if}
        {/if}
        {if $contentvisible}
        {$data.text|safe}
        {/if}
        {if $isverified}
            <div class="verifiedon">{$verifiedon|safe}</div>
        {/if}
    {/if}
    {if $data.addcomment && ($canverify || $canunverify)}
        {if $inedit}<div class="description">{/if}
        {$commentform|safe}
        {if $inedit}</div>{/if}
    {/if}
    {if $commentlist}
        <div id="commentlist{$blockid}" class="commentlist">
        {$commentlist|safe}
        </div>
    {/if}
</div>

{* verification modal form *}
    <div tabindex="0" class="modal fade" id="verification-{$blockid}-confirm-form">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="{str tag=Close}"><span aria-hidden="true">&times;</span></button>
                    <h1 class="modal-title">
                        {str tag=verificationmodaltitle section=blocktype.verification arg1=$title}
                    </h1>
                </div>
                <div class="modal-body">
                    {if $data.lockportfolio}
                        <div class="verify{$blockid} hidden">
                        {if $resetnames}
                            {str tag=verificationchecklistlockingnames section=blocktype.verification arg1=$resetnames}
                        {else}
                            {str tag=verificationchecklistlocking section=blocktype.verification}
                        {/if}
                        </div>
                    {else}
                        <div class="verify{$blockid} hidden">
                        {if $resetnames}
                            {str tag=verificationchecklistnames section=blocktype.verification arg1=$resetnames}
                        {else}
                            {str tag=verificationchecklist section=blocktype.verification}
                        {/if}
                        </div>
                    {/if}
                    <div class="unverify{$blockid} hidden">{str tag=unverify section=blocktype.verification}</div>
                    <div class="btn-group">
                        <button id="verification-{$blockid}-submit-button" type="button" class="btn btn-secondary">{str tag="continue" section="admin"}</button>
                        <button id="verification-{$blockid}-back-button" type="button" class="btn btn-secondary">{str tag="back"}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script type="application/javascript">
$(function() {
    $("#verification-{$blockid}-back-button").on('click', function() {
        $("#verification-{$blockid}-confirm-form").modal('hide');
    });
    var verified{$blockid} = '{$isverified}';
    $('#verify-{$blockid}').on('click', function(event) {
        event.preventDefault();
        event.stopPropagation();
        if (verified{$blockid}) {
            $('.verify{$blockid}').addClass('hidden');
            $('.unverify{$blockid}').removeClass('hidden');
        }
        else {
            $('.verify{$blockid}').removeClass('hidden');
            $('.unverify{$blockid}').addClass('hidden');
        }
        $j("#verification-{$blockid}-confirm-form").modal('show');
    });

    $("#verification-{$blockid}-submit-button").on('click', function(event) {
        event.preventDefault();
        event.stopPropagation();
        sendjsonrequest('{$WWWROOT}blocktype/verification/verify.json.php', { 'blockid': '{$blockid}', 'verify': 1 }, 'POST', function (data) {
            if (data.data) {
                if (data.data.verified) {
                    $('#verify-{$blockid} span.icon').addClass('icon-check-square').removeClass('icon-square icon-regular');
                    {if !$canunverify}
                        $('#verify-{$blockid}').replaceWith($('#verify-{$blockid}').children());
                    {/if}
                    verified{$blockid} = '1';
                    if (!$("#verification-{$blockid} .verifiedon").length) {
                        $('#verification-{$blockid}').append('<div class="verifiedon"></div>');
                    }
                    $('#verification-{$blockid} .verifiedon').html(data.data.verifiedon);
                    $('#verification-{$blockid} .verifiedon').removeClass('hidden');
                }
                else {
                    $('#verify-{$blockid} span.icon').addClass('icon-square icon-regular').removeClass('icon-check-square');
                    verified{$blockid} = '';
                    $('#verification-{$blockid} .verifiedon').addClass('hidden');
                }
            }
            location.reload();
            $("#verification-{$blockid}-confirm-form").modal('hide');
        });
    });
});
</script>
