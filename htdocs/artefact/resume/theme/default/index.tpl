{include file="header.tpl"}

<div id="column-right">
{include file="sidebar.tpl"}
</div>
{include file="columnleftstart.tpl"}
<h3>{str tag='myresume' section='artefact.resume'}</h3>
{$mainform}
<h3>{str tag='employmenthistory' section='artefact.resume'}</h3>
<table id="employmenthistorylist">
    <thead>
        <tr>
            <th>{str tag='startdate' section='artefact.resume'}</th>
            <th>{str tag='enddate' section='artefact.resume'}</th>
            <th>{str tag='position' section='artefact.resume'}</th>
            <th></th>
            <th></th>
        </tr>
    </thead>
</table>
<div>
    <button id="addemploymenthistorybutton" onclick="toggleCompositeForm('employmenthistory');">{str tag='add'}</button>
    <div id="employmenthistoryform" class="hiddenStructure">{$compositeforms.employmenthistory}</div>
</div>
<h3>{str tag='educationhistory' section='artefact.resume'}</h3>
<table id="educationhistorylist">
    <thead>
        <tr>
            <th>{str tag='startdate' section='artefact.resume'}</th>
            <th>{str tag='enddate' section='artefact.resume'}</th>
            <th>{str tag='qualification' section='artefact.resume'}</th>
            <th></th>
            <th></th>
        </tr>
    </thead>
</table>
<div>
    <button id="addeducationhistorybutton" onclick="toggleCompositeForm('educationhistory');">{str tag='add'}</button>
    <div id="educationhistoryform" class="hiddenStructure">{$compositeforms.educationhistory}</div>
</div>
<h3>{str tag='certification' section='artefact.resume'}</h3>
<table id="certificationlist">
    <thead>
        <tr>
            <th>{str tag='date' section='artefact.resume'}</th>
            <th>{str tag='title' section='artefact.resume'}</th>
            <th></th>
            <th></th>
        </tr>
    </thead>
</table>
<div>
    <button id="addcertificationbutton" onclick="toggleCompositeForm('certification');">{str tag='add'}</button>
    <div id="certificationform" class="hiddenStructure">{$compositeforms.certification}</div>
</div>
<h3>{str tag='book' section='artefact.resume'}</h3>
<table id="booklist">
    <thead>
        <tr>
            <th>{str tag='date' section='artefact.resume'}</th>
            <th>{str tag='title' section='artefact.resume'}</th>
            <th></th>
            <th></th>
        </tr>
    </thead>
</table>
<div>
    <button id="addbookbutton" onclick="toggleCompositeForm('book');">{str tag='add'}</button>
    <div id="bookform" class="hiddenStructure">{$compositeforms.book}</div>
</div>
<h3>{str tag='membership' section='artefact.resume'}</h3>
<table id="membershiplist">
    <thead>
        <tr>
            <th>{str tag='startdate' section='artefact.resume'}</th>
            <th>{str tag='enddate' section='artefact.resume'}</th>
            <th>{str tag='title' section='artefact.resume'}</th>
            <th></th>
            <th></th>
        </tr>
    </thead>
</table>
<div>
    <button id="addmembershipbutton" onclick="toggleCompositeForm('membership');">{str tag='add'}</button>
    <div id="membershipform" class="hiddenStructure">{$compositeforms.membership}</div>
</div>
{include file="columnleftend.tpl"}


{include file="footer.tpl"}
