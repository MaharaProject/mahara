{if !$dialog}
{include file='header.tpl'}
{/if}

<h2>{str tag="function" section="auth.webservice"}: {$function->name} </h2>
<table class="table table-striped">
<tr><td>{str tag="wsdocdescription" section="auth.webservice"}:</td><td>{$functiondescription}</td></tr>
<tr><td>{str tag="component" section="auth.webservice"}:</td><td>{$function->component}</td></tr>
<tr><td>{str tag="class" section="auth.webservice"}:</td><td>{$function->classname}</td></tr>
<tr><td>{str tag="method" section="auth.webservice"}:</td><td>{$function->methodname}</td></tr>
</table>
<br/>
<span class='arguments'>{str tag="arguments" section="auth.webservice"}</span>
<br/>

<span style='font-size: 80%'>
<pre class='detaildescription'>
{foreach from=$fdesc->parameters_desc->keys item=paramdesc key=paramname}
<span class="wsname">{$paramname}:</span> {wsdoc_detailed_description_html($paramdesc, 1)}
{/foreach}
</pre>

{foreach from=$fdesc->parameters_desc->keys item=paramdesc key=paramname}
{if $xmlrpcactive == 1 }
   <br/>
   <div>
   <div class="xmlrpcdescription">
<pre class='detaildescription'><b>{str tag="phpparam" section="auth.webservice"}</b>
{wsdoc_xmlrpc($paramname, $paramdesc)}
</pre>
   </div>
   </div>
{/if}
{if $restactive == 1 }
   <br/>
   <div>
   <div class="restdescription">
<pre class='detaildescription'><b>{str tag="restparam" section="auth.webservice"}</b>
{wsdoc_rest($paramname, $paramdesc)}
</pre>
   </div>
   </div>
{/if}
</span>
{/foreach}

<br/>
<br/>
<span class='response'>{str tag="response" section="auth.webservice"}</span>
<br/>
<span style='font-size: 80%'>
{if $fdesc->returns_desc->desc}
{$fdesc->returns_desc->desc}
<br/>
{/if}
{if $fdesc->returns_desc}
   <div>
   <div class="detaildescription">
<pre class='detaildescription'><b>{str tag="generalstructure" section="auth.webservice"}</b>
{wsdoc_detailed_description_html($fdesc->returns_desc)}
</pre>
   </div>
   </div>
{if $xmlrpcactive == 1 }
   <br/>
   <div>
   <div class="xmlrpcdescription">
<pre class='detaildescription'><b>{str tag="phpparam" section="auth.webservice"}</b>
{htmlspecialchars(wsdoc_xmlrpc_param_description_html($fdesc->returns_desc))}
</pre>
   </div>
   </div>
{/if}
{if $restactive == 1 }
   <br/>
   <div>
   <div class="restdescription">
<pre class='detaildescription'><b>{str tag="restcode" section="auth.webservice"}</b>
{wsdoc_rest_response($paramname, $fdesc->returns_desc)}
</pre>
   </div>
   </div>
{/if}
{/if}
</span>
<br/>
{if $restactive == 1 }
   <br/>
   <span class='response'>{str tag="errorcodes" section="auth.webservice"}</span>
   <br/>
   <span style='font-size: 80%'>
   <div>
   <div class="restdescription">
<pre class='detaildescription'><b>{str tag="restexception" section="auth.webservice"}</b>
{wsdoc_rest_exception($paramname, $fdesc->returns_desc)}
</pre>
   </div>
   </div>
   </span>
{/if}
<br/>
<br/>
{$form|safe}

{if !$dialog}
{include file='footer.tpl'}
{/if}