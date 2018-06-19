<div class="card card-secondary">
    <h3 class="card-header">
    	{str tag="friendsdescr" section="account"}
    	<span class="float-right">
    	{contextualhelp plugintype='core' pluginname='account' form='accountprefs' element='friendscontrol'}
    </span>
    </h3>
    <div class="card-body">
    {$sbdata.form|safe}
    </div>
</div>
