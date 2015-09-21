{include file="header.tpl"}
{$typeform|safe}

<div class="panel panel-default panel-body mtl">
    {$buttonformopen|safe}
    {$buttonform|safe}
        <table id="suspendedlist" class="table fullwidth pull-left ptl">
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
