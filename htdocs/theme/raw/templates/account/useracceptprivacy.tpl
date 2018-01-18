{include file="header.tpl"}

{if $loginanyway}
    <p class="lead">
    {$loginanyway|safe}
    </p>
{/if}
<div class="lead">{str tag="newprivacy" section="admin"}</div>
<div>{$form|safe}</div>

{* Modal form *}
    <div tabindex="0" class="modal fade" id="privacy-confirm-form">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">
                        {str tag=refuseprivacy section=admin}
                    </h4>
                </div>
                <div class="modal-body">
                    <p><strong>{str tag=privacyrefusaldetails section=admin}</strong></p>
                    <p>{str tag=confirmprivacyrefusal section=admin}</p>
                    <div class="btn-group">
                        <button id="confirm-no-button" type="button" class="btn btn-default">{str tag="yes"}</button>
                        <button id="back-button" type="button" class="btn btn-default">{str tag="no"}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script type="application/javascript">
    var acceptprivacy = false;
    $j("#agreetoprivacy").on('submit', function(event) {
        if ($j("#agreetoprivacy input:checkbox").length == $j("#agreetoprivacy input:checkbox:checked").length) {
            acceptprivacy = true;
        }
        if (!acceptprivacy) {
            event.preventDefault();
            event.stopPropagation();
            processingStop();
            $j("#privacy-confirm-form").modal('show');
        }
    });

    $j("#confirm-no-button").on('click', function() {
        acceptprivacy = true;
        $j("#privacy-confirm-form").modal('hide');
        formAbortProcessing($j("#agreetoprivacy_submit"));
        $j('<input />').attr('type', 'hidden').attr('name', "hasrefused").attr('value', "1").appendTo('#agreetoprivacy');

        // settimeout to 0 so it waits for everything else to finish before trigger the submit button
        setTimeout(function() {
            $j('#agreetoprivacy_submit').trigger( "click" );
        }, 0);
    });

    $j("#back-button").on('click', function() {
        formAbortProcessing($j("#agreetoprivacy_submit"));
        $j("#privacy-confirm-form").modal('hide');
    });

    $('.modal').on('shown.bs.modal', function() {
        $('#confirm-no-button').focus();
    });
    $('.modal').on('hidden.bs.modal', function() {
        if (!acceptprivacy) {
            formAbortProcessing($j("#agreetoprivacy_submit"));
            $('#agreetoprivacy_submit').focus();
        }
    });
    </script>

{include file="footer.tpl"}
