<div class="card">
    <h2 class="card-header">
        {str tag="friendsdescr" section="account"}
        <span class="float-end">
        {contextualhelp plugintype='core' pluginname='account' form='accountprefs' element='friendscontrol'}
    </span>
  </h2>
  <div class="card-body">
  {$sbdata.form|safe}
  </div>
</div>
