Ext.onReady(function () {
 var mainPanel = Ext.getCmp('modx-panel-resource');
 if (mainPanel.config.record.id > 0) {

  FileAttach.config.docid = mainPanel.config.record.id;

  MODx.addTab("modx-resource-tabs", {
   title: _('files'),
   id: "files-tab",
   width: "95%",
   items: [{
	xtype: 'fileattach-grid-items',
	cls: 'main-wrapper',
	width: "95%"
   }]
   });
 }
});
