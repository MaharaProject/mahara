<h3 class="title">{str tag='idptable' section='auth.saml'}</h3>
<table id="idplist" class="table table-striped listing">
    <thead>
        <tr>
        {foreach from=$cols item=col}
            <th>{$col['name']}</th>
        {/foreach}
        </tr>
    </thead>
    <tbody>
    {$html|safe}
    </tbody>
</table>

<script>
function deleteidp(el, idp) {
    var data = { 'idp' : idp };
    var row = $(el).closest('tr');
    if (confirm("{get_string('confirmdeleteidp', 'auth.saml')}")) {
        sendjsonrequest(config.wwwroot + 'auth/saml/idpdelete.json.php', data, 'POST', function(data) {
            if (data.data.error) {
                alert(data.data.error);
            }
            else {
                row.hide();
            }
        });
    }
}
</script>
