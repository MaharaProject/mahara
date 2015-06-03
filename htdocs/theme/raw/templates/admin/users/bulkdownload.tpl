{include file="header.tpl"}
<div id="exportgeneration">
    <h3>{str tag=pleasewaitwhileyourexportisbeinggenerated section=export}</h3>
    <iframe src="{$WWWROOT}admin/users/bulkdownload.php" id="progress" scrolling="no" frameborder="none"></iframe>
</div>
{include file="footer.tpl"}
