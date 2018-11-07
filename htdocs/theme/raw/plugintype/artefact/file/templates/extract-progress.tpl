{include file="header.tpl"}
<div id="extract">
    <h3>{str tag=pleasewaitwhileyourfilesarebeingunzipped section=artefact.file}</h3>
    <iframe src="{$WWWROOT}artefact/file/extract-progress.php" id="progress-iframe"></iframe>
</div>
{include file="footer.tpl"}
