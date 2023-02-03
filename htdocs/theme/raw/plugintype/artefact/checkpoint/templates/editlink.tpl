<button id="modify_checkpoint_feedback_{$block}"
    class="js-peercheckpoint-modal feedback assessbtn btn btn-secondary btn-sm" type="button"
    data-bs-toggle="modal-docked" data-bs-target="#checkpoint_feedback_form_{$block}" data-blockid="{$block}"
data-id="{$id}">
    <span class="icon icon-pencil-alt" role="presentation" aria-hidden="true"></span>
    <span class="visually-hidden">{str tag=editspecific arg1=$title}</span>
</button>

<script type="application/javascript">
$('#modify_checkpoint_feedback_{$block}').on('click', function (e) {
    var blockid = $(this).data('blockid');
    var feedbackid = $(this).data('id');
    var formname = $('#add_checkpoint_feedback_form_' + blockid);
    formname = formname.find('form')[0].id;
    dock.show($('#checkpoint_' + blockid), false, true);
    if ($(this).data('id')) {
      sendjsonrequest(config.wwwroot + 'artefact/checkpoint/checkpointinfo.json.php', {
        'id': $(this).data('id'),
        'block': blockid,
        }, 'POST', function (data) {
        // Populate the form
        $('#' + formname + '_message').val(data.data);
        $('#' + formname + '_feedback').val(feedbackid);
        // Update TinyMCE
        modifyTinyMCEContent(formname, data, data.data);
      });
    }
});
</script>
