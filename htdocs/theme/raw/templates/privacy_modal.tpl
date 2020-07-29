{* Modal form *}
    <div tabindex="0" class="modal fade" id="privacy-confirm-form">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn close" data-dismiss="modal" aria-label="{str tag=Close}"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">
                        {str tag=refuseprivacy section=admin}
                    </h4>
                </div>
                <div class="modal-body">
                    <p><strong>{str tag=privacyrefusaldetails section=admin}</strong></p>
                    <p class="reason">
                        <textarea id="reason" rows="4" cols="65" placeholder="{str tag=enterreason section=admin}"></textarea>
                    </p>
                    <p>{str tag=confirmprivacyrefusal section=admin}</p>
                    <div class="btn-group">
                        <button id="confirm-no-button" type="button" class="btn btn-secondary">{str tag="yes"}</button>
                        <button id="back-button" type="button" class="btn btn-secondary">{str tag="no"}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
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
        $j('<input></input>').attr('type', 'hidden').attr('name', "hasrefused").attr('value', "1").appendTo('#agreetoprivacy');
        var reason = encodeURIComponent($j('#reason').val());
        $j('<input></input>').attr('class', 'js-hidden').attr('name', "reason").attr('value', reason).appendTo('#agreetoprivacy');
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
        $('#confirm-no-button').trigger("focus");
    });
    $('.modal').on('d-none.bs.modal', function() {
        if (!acceptprivacy) {
            formAbortProcessing($j("#agreetoprivacy_submit"));
            $('#agreetoprivacy_submit').trigger("focus");
        }
    });
    </script>
