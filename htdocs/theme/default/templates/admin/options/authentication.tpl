{include file="header.tpl"}

<div class="content">
<div class="box-cnrs"><span class="cnr-tl"><span class="cnr-tr"><span 
class="cnr-bl"><span class="cnr-br">
	<div class="maincontent">
	
<p><strong>TODO</strong> for this page:

<ul>
    <li>Get a list of installed authentication methods (depends: database)</li>
    <li>For each one, a list of institutions using it (depends: database)</li>
</ul></p>

<h2>{$title}</h2>

{$description}

<table>
    <tr>
        <th>Authentication Method</th>
        <th>Institutions <a href="#" title="Which institutions are using the authentication method">?</a></th>
    </tr>
    {if $methods}
    {foreach from=$methods item=method}
    <tr>
        {assign var="tag" value=$method->name}
        {assign var="section" value=auth.$tag}
        <td><a href="authenticationoptions.php?m={$method->name|escape}">{str section=$section tag=$tag}</a></td>
        <td>{foreach from=$method->institutions item=institution}{$institution|escape}<br>{/foreach}</td>
    </tr>
    {/foreach}
    {/if}
</table>

<p><a href="..">parent</a></p>

	</div>
</span></span></span></span></div>	
</div>

{include file="footer.tpl"}
