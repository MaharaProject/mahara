{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}
			<h2>{str tag="preferences"}</h2>
	
			{$form}

                        <h4>{str tag="institutionmembership"}</h4>
{$memberform}
{$requestedform}
{$inviteform}
{$joinform}
{include file="columnleftend.tpl"}
{include file="footer.tpl"}
