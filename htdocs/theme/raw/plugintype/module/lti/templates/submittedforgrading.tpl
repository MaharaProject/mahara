{include file="header.tpl"}

<p>{str tag='portfoliosubmittedforgrading' section='module.lti' arg1=$link arg2=$title arg3=$timesubmitted|strtotime|format_date}</p>

{$revokeform|safe}

{if $timegraded}
    <p>{str tag='gradereceived' section='module.lti' arg1=$grade arg2=$gradedby arg3=$timegraded|strtotime|format_date}</p>
{/if}
{include file="footer.tpl"}