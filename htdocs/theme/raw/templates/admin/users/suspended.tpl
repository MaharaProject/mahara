{include file="header.tpl"}
<div class="panel panel-default panel-body">
    {$typeform|safe}
    {$buttonformopen|safe}
        {$buttonform|safe}
        <table id="suspendedlist" class="table fullwidth mtxl pull-left">
            <thead>
                <tr>
                    <th>{str tag=fullname}</th>
                    <th>{str tag=institution}</th>
                    <th>{str tag=studentid}</th>
                    <th>{str tag=suspendingadmin section=admin}</th>
                    <th>{str tag=suspensionreason section=admin}</th>
                    <th>{str tag=expired section=admin}</th>
                    <th>{str tag=select}</th>
                </tr>
                </thead>
                <tbody>
                </tbody>
        </table>
    </form>
</div>
{include file="footer.tpl"}
