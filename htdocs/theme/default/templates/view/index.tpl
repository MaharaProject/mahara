{include file="header.tpl"}

{include file="searchbox.tpl"}

{include file="adminmenu.tpl"}

<div class="content">
    <div id="views">
        <div style="text-align:right;">
            <input type="button" value="{str tag="createnewview"}" onclick="window.location='create1.php';">
        </div>
        <table id="viewlist">
            <tbody>
            </tbody>
        </table>
    </div>
</div>

{include file="footer.tpl"}
