{include file="header.tpl"}

{include file="adminmenu.tpl"}

<div class="content">
    <h2>{str tag="mygroups"}</h2>

    <a href="create.php">{str tag="creategroup"}</a>

    <table id="grouplist">
        <thead>
            <tr>
                <th>{str tag="groupname"}</th>
                <th>{str tag="membercount"}</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>

{include file="footer.tpl"}
