{include file="header.tpl"}
<div id="exportgeneration" class="text-center">
    <h2>{str tag=pleasewaitwhileyourexportisbeinggenerated section=export}</h2>
    <iframe src="{$WWWROOT}admin/users/bulkdownload.php" id="progress-iframe"></iframe>
</div>
{include file="footer.tpl"}
