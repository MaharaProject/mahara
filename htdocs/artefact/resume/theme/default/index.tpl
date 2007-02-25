{include file="header.tpl"}

<div id="column-right">
{include file="sidebar.tpl"}
</div>
{include file="columnleftstart.tpl"}
<h3>{str tag='myresume' section='artefact.resume'}</h3>
{$mainform}
{include file="artefact:resume:fragments/employmenthistory.tpl" controls="true"}
{include file="artefact:resume:fragments/educationhistory.tpl" controls="true"}
{include file="artefact:resume:fragments/certification.tpl" controls="true"}
{include file="artefact:resume:fragments/book.tpl" controls="true"}
{include file="artefact:resume:fragments/membership.tpl" controls="true"}
{include file="columnleftend.tpl"}
{include file="footer.tpl"}
