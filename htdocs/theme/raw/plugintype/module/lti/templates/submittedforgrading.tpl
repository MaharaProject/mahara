{include file="header.tpl"}

<p>{str tag='portfoliosubmitted' section='module.lti' arg1=$title arg2=$timesubmitted|strtotime|format_date}</p>
{if $timegraded}
    <p>{str tag='gradereceived' section='module.lti' arg1=$grade arg2=$gradedby arg3=$timegraded|strtotime|format_date}</p>
{/if}
{include file="footer.tpl"}