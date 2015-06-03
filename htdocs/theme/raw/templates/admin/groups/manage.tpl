{include file="header.tpl"}
            <h3>{str tag=groupquotas section=admin args=$groupname}</h3>
            <p>{str tag=managegroupquotadescription1 section=admin}</p>
            <div>
            {$quotasform|safe}
            </div>
            <h3>{str tag=groupadminsforgroup section=admin args=$groupname}</h3>
            <p>{str tag=managegroupdescription1 section=admin}</p>
            <div class="userlistform">
			{$managegroupform|safe}
            </div>
{include file="footer.tpl"}
