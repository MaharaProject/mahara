{include file="header.tpl"}

{include file="adminmenu.tpl"}

<div class="content">
    <table id="searchresults">
        <thead>
            <tr>
                <th>{str section=artefact.internal tag="firstname"}</th>
                <th>{str section=artefact.internal tag="lastname"}</th>
                <th>{str section=artefact.internal tag="preferredname"}</th>
                <th>{str section=artefact.internal tag="email"}</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>

{include file="footer.tpl"}
