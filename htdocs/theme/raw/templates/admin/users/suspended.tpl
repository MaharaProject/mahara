{include file="header.tpl"}

{include file="columnfullstart.tpl"}
            {$buttonformopen}
            {$buttonform}
            <table id="suspendedlist" class="fullwidth">
                <thead>
                    <tr>
                        <th>{str tag=fullname}</th>
                        <th>{str tag=institution}</th>
                        <th>{str tag=studentid}</th>
                        <th>{str tag=suspendingadmin section=admin}</th>
                        <th>{str tag=suspensionreason section=admin}</th>
                        <th>{str tag=select}</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
            </form>

{include file="columnfullend.tpl"}

{include file="footer.tpl"}
