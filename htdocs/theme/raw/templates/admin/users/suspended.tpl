{include file="header.tpl"}
{$typeform|safe}

<div class="card card-body view-container table-responsive">
    {$buttonformopen|safe}
    {$buttonform|safe}
        <table id="suspendedlist" class="table table-striped fullwidth listing">
            <thead>
                <tr>
                    <th>{str tag=fullname}</th>
                    <th>{str tag=institution}</th>
                    <th>{str tag=studentid}</th>
                    <th>{str tag=suspendingadmin section=admin}</th>
                    <th>{str tag=suspensionreason section=admin}</th>
                    <th>{str tag=expired section=admin}</th>
                    <th><div class="headhtml allnone-toggles">
                            <div class="btn-group" role="group">
                                <a class="btn btn-sm btn-secondary" href="" id="selectall">{str tag='All'}</a><a class="btn active btn-sm btn-secondary" href="" id="selectnone">{str tag='none'}</a>
                            </div>
                        </div>
                    </th>
                </tr>
            </thead>
            <tbody>
            {$suspendhtml|safe}
            </tbody>
        </table>
    </form>
</div>

{$pagination|safe}
{include file="footer.tpl"}
