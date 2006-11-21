{include file="header.tpl"}

{include file="adminmenu.tpl"}

<div class="content">
    <div class="searchform">
        <h2>{str tag="search"}</h2>
        {$SEARCHFORM}
    </div>
{if ($QUERYPOSTED)}
    <div id="results">
{else}
    <div id="results" style="display:none;">
{/if}
        <h2>{str tag="results"}</h2>
        <table id="searchresults">
            <thead>
                <tr>
                    <th>{str section=artefact.internal tag="preferredname"}</th>
                    <th>{str section=artefact.internal tag="firstname"}</th>
                    <th>{str section=artefact.internal tag="lastname"}</th>
                    <th>{str section=artefact.internal tag="email"}</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>

{include file="footer.tpl"}
