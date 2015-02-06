FileAttach.page.Home = function (config) {
	config = config || {};
	Ext.applyIf(config, {
		components: [{
			xtype: 'fileattach-panel-home', renderTo: 'fileattach-panel-home-div'
		}]
	});
	FileAttach.page.Home.superclass.constructor.call(this, config);
};
Ext.extend(FileAttach.page.Home, MODx.Component);
Ext.reg('fileattach-page-home', FileAttach.page.Home);

FileAttach.panel.Home = function (config) {
	config = config || {};
	Ext.apply(config, {
		baseCls: 'modx-formpanel',
		layout: 'anchor',
		hideMode: 'offsets',
		items: [{
			html: '<h2>' + _('fileattach') + '</h2>',
			cls: '',
			style: {margin: '15px 0'}
		}, {
				layout: 'anchor',
				items: [{
					html: _('fileattach.intro_msg'),
					cls: 'panel-desc',
				}, {
					xtype: 'fileattach-grid-items',
					cls: 'main-wrapper',
				}]
		}]
	});
	FileAttach.panel.Home.superclass.constructor.call(this, config);
};
Ext.extend(FileAttach.panel.Home, MODx.Panel);
Ext.reg('fileattach-panel-home', FileAttach.panel.Home);
