{include file="header.tpl"}

{if $originaltitle}
    <p>{str tag='portfoliosubmittedforgrading1' section='module.lti' arg1=$link arg2=$originaltitle arg3=$timesubmitted|strtotime|format_date}</p>
{else}
    <p>{str tag='portfoliosubmittedforgradingoriginaldelete' section='module.lti' arg1=$link arg2=$timesubmitted|strtotime|format_date}</p>
{/if}

{$revokeform|safe}

{if $timegraded}
    <p>{str tag='gradereceived' section='module.lti' arg1=$grade arg2=$gradedby arg3=$timegraded|strtotime|format_date}</p>
{/if}
{include file="footer.tpl"}