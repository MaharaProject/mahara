{include file="header.tpl"}
{if $cancreate}
    <div class="btn-top-right btn-group btn-group-top">
        <a href="{$WWWROOT}group/edit.php" class="btn btn-secondary creategroup">
            <span class="icon icon-plus left" role="presentation" aria-hidden="true"></span>
            {str tag="creategroup" section="group"}
        </a>
    </div>
{/if}
{$form|safe}
{if $groups}
    <div class="card view-container">
        <h2 class="card-header">{str tag=Results}</h2>
        {if $activegrouplabels}
        <div class="activegrouplabels">
        {$activegrouplabels|safe}
        </div>
        {/if}
        <div id="findgroups" class="list-group list-group-top-border">
            {$groupresults|safe}
        </div>
    </div>
    {$pagination|safe}
    <script>
    function wire_labels() {
        jQuery('.label-btn').each(function(k, label) {
            $(label).off('click');
            $(label).on('click', function(e) {
                e.stopPropagation();
                e.preventDefault();
                var params = { 'groupid': $(label).data('id') };
                sendjsonrequest(config.wwwroot + 'group/label.json.php', params, 'POST', function (data) {
                    $('#group-label-modal .modal-body .labelform').html(data.html);
                    $('#group-label-modal').off('hidden.bs.modal');
                    $('#group-label-modal').on('hidden.bs.modal', function () {
                        $('#messages').find('.alert-danger').hide();
                    });
                    $('#group-label-modal').modal('show');
                });
            });
        });
    }

    function group_label_update(form, data) {
        if (data.returnCode == 0) {
            formSuccess(form, data);
            $('#group-label-modal').modal('hide');
            $('#' + data.data.id).replaceWith(data.data.html);
            wire_labels();
        }
        else {
            $('#grouplabel').prepend('<div class="alert alert-danger">' + data.message + '</div>');
            formError(form, data);
        }
    }
    // When paginating
    $(document).on('pageupdated', function(e, data) {
        wire_labels();
    });
    // On initial page load
    wire_labels();

    {if $pagination_js}
        {$pagination_js|safe}
    {/if}
    </script>
{else}
    {if $activegrouplabels}
    <div class="activegrouplabels noresults">
    {$activegrouplabels|safe}
    </div>
    {/if}
    <p class="no-results">
        {str tag="nogroupsfound" section="group"}
        {str tag="trysearchingforgroups1" section="group" arg1=$WWWROOT}
    </p>
{/if}
<script>
$('#dummy_grouplabelfilter').on('select2:select', function (e) {
    var encoded = encodeURIComponent(e.params.data.text);
    window.location.href = '{$paramsurl}&labelfilter=' + encoded;
});
$('#dummy_grouplabelfilter').on('select2:unselect', function (e) {
    var encoded = encodeURIComponent(e.params.data.text);
    window.location.href = '{$paramsurl}&labelfilter=' + encoded + '&remove=1';
});
</script>
{include file="group/grouplabelmodal.tpl"}
{include file="footer.tpl"}
