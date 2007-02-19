{include file="header.tpl"}

<div id="column-right">
{include file="adminmenu.tpl"}
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
        </tr>
    </thead>
</table>
<button>{str tag='add'}</button>
<h3>{str tag='educationhistory' section='artefact.resume'}</h3>
<table id="educationhistorylist">
    <thead>
        <tr>
            <th>{str tag='startdate' section='artefact.resume'}</th>
            <th>{str tag='enddate' section='artefact.resume'}</th>
            <th>{str tag='qualification' section='artefact.resume'}</th>
        </tr>
    </thead>
</table>
<button>{str tag='add'}</button>
<h3>{str tag='certification' section='artefact.resume'}</h3>
<table id="certificationlist">
    <thead>
        <tr>
            <th>{str tag='date' section='artefact.resume'}</th>
            <th>{str tag='description' section='artefact.resume'}</th>
        </tr>
    </thead>
</table>
<button>{str tag='add'}</button>
<h3>{str tag='book' section='artefact.resume'}</h3>
<table id="booklist">
    <thead>
        <tr>
            <th>{str tag='date' section='artefact.resume'}</th>
            <th>{str tag='title' section='artefact.resume'}</th>
        </tr>
    </thead>
</table>
<button>{str tag='add'}</button>
<h3>{str tag='membership' section='artefact.resume'}</h3>
<table id="membershiplist">
    <thead>
        <tr>
            <th>{str tag='startdate' section='artefact.resume'}</th>
            <th>{str tag='enddate' section='artefact.resume'}</th>
            <th>{str tag='title' section='artefact.resume'}</th>
        </tr>
    </thead>
</table>
<button>{str tag='add'}</button>
{include file="columnleftend.tpl"}


{include file="footer.tpl"}
