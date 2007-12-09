{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}
			<h2>{str tag="preferences"}</h2>
	
			{$form}

                        <h3>{str tag="institutionmembership"}</h3>
{$memberform}
{$requestedform}
{$invitedform}
{$joinform}
{include file="columnleftend.tpl"}
{include file="footer.tpl"}
