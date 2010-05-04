{auto_escape off}
{include file="header.tpl"}

            {$buttonformopen}
            {$buttonform}
            <table id="suspendedlist" class="table fullwidth">
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

{include file="footer.tpl"}
{/auto_escape}
