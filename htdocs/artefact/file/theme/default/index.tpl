{include file="header.tpl"}
{include file="adminmenu.tpl"}

<div class="content">
    <h2>{str section="artefact.file" tag="myfiles"}</h2>
    <div id="uploader"></div>
    <div id="foldernav"></div>
    <table id="filelist">
        <thead><tr>
            <th>Name</th>
            <th>Size</th>
            <th>Time</th>
            <th></th>
        </tr></thead>
        <tbody><tr><th></th></tr></tbody>
    </table>
</div>

{include file="footer.tpl"}
