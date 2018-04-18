<div class="card card-default">
    <h3 class="card-heading">
    	{str tag="friendsdescr" section="account"}
    	<span class="float-right">
    	{contextualhelp plugintype='core' pluginname='account' form='accountprefs' element='friendscontrol'}
    </span>
    </h3>
    <div class="card-body">
    {$sbdata.form|safe}
    </div>
</div>
