{include file="header.tpl"}

{include file="adminmenu.tpl"}

<div class="content">
    <h2>{str tag="search"}</h2>
    {$SEARCHFORM}
    <h2>{str tag="results"}</h2>
    <table id="searchresults">
        <thead>
            <tr>
                <th>{str section=artefact.internal tag="name"}</th>
                <th>{str section=artefact.internal tag="institution"}</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>

{include file="footer.tpl"}
