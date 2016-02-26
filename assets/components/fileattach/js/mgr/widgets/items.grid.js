FileAttach.utils.renderBoolean = function (value, props, row) {
    return value
		? String.format('<span class="green">{0}</span>', _('yes'))
		: String.format('<span class="red">{0}</span>', _('no'));
}

FileAttach.utils.getMenu = function (grid) {
 var menu = [];

 menu.push({handler: grid['updateItem'], text: _('update')});
 menu.push({handler: grid['resetItem'], text: _('reset')});
 menu.push('-');
 menu.push({handler: grid['removeItem'], text: _('remove')});

 return menu;
};

FileAttach.window.UpdateItem = function (config) {
    config = config || {};
    if (!config.id) {
    	config.id = 'fileattach-item-window-update';
    }
    Ext.applyIf(config, {
	title: _('update'),
	width: 550,
	autoHeight: true,
	url: FileAttach.config.connector_url,
	action: 'mgr/update',
	fields: this.getFields(config),
	keys: [{
		key: Ext.EventObject.ENTER, shift: true, fn: function () {
			this.submit()
		}, scope: this
	}]
    });

    FileAttach.window.UpdateItem.superclass.constructor.call(this, config);
};
Ext.extend(FileAttach.window.UpdateItem, MODx.Window, {
	calcHash: function (btn, e, row) {
	    btn.hide();
	    MODx.Ajax.request({
			url: this.config.url,
			params: {
			    action: 'mgr/hash',
			    id: this.config.record.object.id
			},
			listeners: {
			    success: {
				fn: function (r) {
				    Ext.getCmp(this.config.id + '-hash').setValue(r.object.hash);
				    Ext.getCmp(this.config.id + '-hash').show();
				    }, scope: this},
			    fail: {
				fn: function () {
				    btn.show();
				}, scope: this}
			}
	    });
	},

	getFields: function (config) {
	    var fields = [{
			xtype: 'hidden',
			name: 'id',
			id: config.id + '-id',
		}, {
			xtype: 'textfield',
			fieldLabel: _('name'),
			name: 'name',
			id: config.id + '-name',
			anchor: '100%',
			allowBlank: false
		}, {
			xtype: 'textfield',
			fieldLabel: _('description'),
			name: 'description',
			id: config.id + '-description',
			anchor: '100%',
		}, {
        		xtype: 'statictextfield',
			fieldLabel: _('fileattach.hash'),
			id: config.id + '-hash',
			name: 'hash',
			hidden: (config.record.object.hash == ''),
			anchor: '100%'
		}, {
        		xtype: 'statictextfield',
			fieldLabel: _('fileattach.fid'),
			name: 'fid',
			id: config.id + '-fid',
			anchor: '100%',
		}];
	
	if (config.record.object.hash == '')
	    fields.push([{
			xtype: 'button',
			text: _('fileattach.calculate'),
			handler: this.calcHash,
			scope: this
		}]);

	fields.push([{
	    xtype: 'xcheckbox',
	    id: config.id + '-private',
	    boxLabel: _('private'),
	    hideLabel: true,
	    name: 'private'
	}]);

	if (FileAttach.config.docid > 0) 
	    fields.push({xtype: 'hidden', name: 'docid', id: config.id + '-docid'});
	else {
	    fields.unshift({
			xtype: 'modx-combo',
			id: config.id + '-docid',
			fieldLabel: _('resource'),
			name: 'docid',
    			hiddenName: 'docid',
			url: FileAttach.config.connector_url,
    			baseParams: {
    			    action: 'mgr/searchres'
    			},
			fields: ['id','pagetitle','description'],
			displayField: 'pagetitle',
			anchor: '100%',
    			pageSize: 10,
    			editable: true,
    			typeAhead: true,
			allowBlank: false,
			forceSelection: true,
    			tpl: new Ext.XTemplate('<tpl for="."><div class="x-combo-list-item"><span style="font-weight: bold">{pagetitle}</span>',
            '<tpl if="description"><br/><span style="font-style:italic">{description}</span></tpl>', '</div></tpl>')
		});
	    }
	    return fields;
	}

});
Ext.reg('fileattach-item-window-update', FileAttach.window.UpdateItem);

FileAttach.grid.Items = function (config) {
	config = config || {};
	if (!config.id) {
		config.id = 'fileattach-grid-items';
	}
	this.sm = new Ext.grid.CheckboxSelectionModel();
	Ext.applyIf(config, {
		url: FileAttach.config.connector_url,
		fields: this.getFields(config),
		columns: this.getColumns(config),
		ddText: _('fileattach.ddtext'),
		tbar: this.getTopBar(config),
		sm: this.sm,
		baseParams: {
			action: 'mgr/getlist',
			docid: FileAttach.config.docid
		},
		listeners: {
			rowDblClick: function (grid, rowIndex, e) {
			    var row = grid.store.getAt(rowIndex);
			    this.updateItem(grid, e, row);
			}
		},
		viewConfig: {
			forceFit: true,
			enableRowBody: true,
			autoFill: true,
			showPreview: true,
			scrollOffset: 0
		},
		paging: true,
		remoteSort: true,
		autoHeight: true,
	});

	// Enable D&D only in resource editor
	if (FileAttach.config.docid)
	    Ext.applyIf(config, {
    		plugins: [new Ext.ux.dd.GridDragDropRowOrder({
        	    copy: false,
        	    scrollable: true,
        	    targetCfg: {},
        	    listeners: {
            		'afterrowmove': {fn:this.onAfterRowMove, scope:this}
        	    }
    		})]
	});

	FileAttach.grid.Items.superclass.constructor.call(this, config);

	// Clear selection on grid refresh
	this.store.on('load', function () {
		if (this._getSelectedIds().length) {
			this.getSelectionModel().clearSelections();
		}
	}, this);
};
Ext.extend(FileAttach.grid.Items, MODx.grid.Grid, {
	windows: {},

	getMenu: function (grid, rowIndex) {
		var row = grid.getStore().getAt(rowIndex);
		var menu = FileAttach.utils.getMenu(this);

		this.addContextMenuItem(menu);
	},

	updateItem: function (btn, e, row) {
		if (typeof(row) != 'undefined') {
			this.menu.record = row.data;
		}
		else if (!this.menu.record) {
			return false;
		}
		var id = this.menu.record.id;

		MODx.Ajax.request({
			url: this.config.url,
			params: {
				action: 'mgr/get',
				docid: FileAttach.config.docid,
				id: id
			},
			listeners: {
				success: {
					fn: function (r) {
						var w = MODx.load({
							xtype: 'fileattach-item-window-update',
							id: Ext.id(),
							record: r,
							listeners: {
								success: {
									fn: function () {
										this.refresh();
									}, scope: this
								}
							}
						});
						w.reset();
						w.setValues(r.object);
						w.show(e.target);
					}, scope: this
				}
			}
		});
	},

	accessItem: function (act, btn, e) {
		var ids = this._getSelectedIds();
		if (!ids.length) {
			return false;
		}

		MODx.Ajax.request({
			url: this.config.url,
			params: {
			    action: 'mgr/access',
			    private: (act.name == 'close')? 1:0,
			    ids: Ext.util.JSON.encode(ids),
			},
			listeners: {
				success: {
					fn: function (r) {
						this.refresh();
					    }, scope: this
					 },
				failure: {fn: function (r) {}, scope: this}
			}
		});


		return true;
	},

	resetItem: function (act, btn, e) {
		var ids = this._getSelectedIds();
		if (!ids.length) {
			return false;
		}
		MODx.msg.confirm({
			title: ids.length > 1
				? _('reset')
				: _('reset'),
			text: ids.length > 1
				? _('fileattach.resets_confirm')
				: _('fileattach.reset_confirm'),
			url: this.config.url,
			params: {
				action: 'mgr/reset',
				ids: Ext.util.JSON.encode(ids),
			},
			listeners: {
				success: {
					fn: function (r) {
						this.refresh();
					}, scope: this
				}
			}
		});
		return true;
	},

	removeItem: function (act, btn, e) {
		var ids = this._getSelectedIds();
		if (!ids.length) {
			return false;
		}
		MODx.msg.confirm({
			title: ids.length > 1
				? _('remove')
				: _('remove'),
			text: ids.length > 1
				? _('confirm_remove')
				: _('confirm_remove'),
			url: this.config.url,
			params: {
				action: 'mgr/remove',
				ids: Ext.util.JSON.encode(ids)
			},
			listeners: {
				success: {
					fn: function (r) {
						this.refresh();
					}, scope: this
				}
			}
		});
		return true;
	},

	uploadFiles: function(btn,e) {
	    if (!this.uploader) {
		aVer = MODx.config.version.split('.');
		uploaddialog = ((aVer[0] == 2) && aVer[1] >= 3)? MODx.util.MultiUploadDialog.Dialog : Ext.ux.UploadDialog.Dialog;

        	this.uploader = new uploaddialog({
		    title: _('upload'),
		    url: this.config.url,
		    base_params: {
                	action: 'mgr/upload',
			docid: FileAttach.config.docid
            	    },
                cls: 'ext-ux-uploaddialog-dialog modx-upload-window'
            });
            this.uploader.on('hide', this.refresh,this);
            this.uploader.on('close', this.refresh,this);
        }

	// Automatically open picker
	this.uploader.show(btn);
        this.uploader.buttons[0].input_file.dom.click();
    },

	getFields: function (config) {
		return ['id', 'name', 'description', 'docid', 'download', 'private', 'pagetitle', 'username', 'rank'];
	},

	getColumns: function (config) {
	    var columns = [this.sm, {
			header: _('fileattach.rank'),
			dataIndex: 'rank',
			hidden: true,
			width: 50
		}, {
			header: _('id'),
			dataIndex: 'id',
			sortable: true,
			width: 50
		}, {
			header: _('name'),
			dataIndex: 'name',
			sortable: true,
			width: 200,
		}, {
			header: _('description'),
			dataIndex: 'description',
			sortable: true,
			width: 200,
		}, {
			header: _('fileattach.downloads'),
			dataIndex: 'download',
			sortable: true,
		}, {
			header: _('private'),
			dataIndex: 'private',
			sortable: true,
			renderer: FileAttach.utils.renderBoolean
		}];

	    if (!FileAttach.config.docid)
		columns.push({
			header: _('resource'),
			dataIndex: 'pagetitle',
			sortable: true
		}, {
			header: _('user'),
			dataIndex: 'username',
			sortable: true
		});

	    return columns;
	},

	getTopBar: function (config) {
	    var fields = [];

	    if (FileAttach.config.docid)
		fields.push({
			xtype: 'button',
			cls: 'primary-button',
			text: _('upload'),
			handler: this.uploadFiles,
			scope: this
		});

		fields.push({
			xtype: 'splitbutton',
			text: _('remove'),
			menu: [{
			 name: 'open',
			 text: _('open'),
			 handler: this.accessItem,
			 scope: this
			}, {
			name: 'close',
			 text: _('close'),
			 handler: this.accessItem,
			 scope: this
			}, '-', {
			 text: _('reset'),
			 handler: this.resetItem,
			 scope: this
			}, {
			 text: _('remove'),
			 handler: this.removeItem,
			 scope: this
			}],
			text: _('bulk_actions')
		}, '->', {
			xtype: 'textfield',
			name: 'uid',
			width: 200,
			id: config.id + '-search-uid-field',
			emptyText: _('user'),
			listeners: {
				render: {
					fn: function (tf) {
						tf.getEl().addKeyListener(Ext.EventObject.ENTER, function () {
							this._doSearch(tf);
						}, this);
					}, scope: this
				}
			}
		}, {
			xtype: 'textfield',
			name: 'query',
			width: 200,
			id: config.id + '-search-field',
			emptyText: _('search'),
			listeners: {
				render: {
					fn: function (tf) {
						tf.getEl().addKeyListener(Ext.EventObject.ENTER, function () {
							this._doSearch(tf);
						}, this);
					}, scope: this
				}
			}
		}, {
			xtype: 'button',
			id: config.id + '-search-clear',
			text: '<i class="icon icon-times"></i>',
			listeners: {
				click: {fn: this._clearSearch, scope: this}
			}
		});

	    return fields;
	},

	onClick: function (e) {
		var elem = e.getTarget();
		if (elem.nodeName == 'BUTTON') {
			var row = this.getSelectionModel().getSelected();
			if (typeof(row) != 'undefined') {
				var action = elem.getAttribute('action');
				if (action == 'showMenu') {
					var ri = this.getStore().find('id', row.id);
					return this._showMenu(this, ri, e);
				}
				else if (typeof this[action] === 'function') {
					this.menu.record = row.data;
					return this[action](this, e);
				}
			}
		}
		return this.processEvent('click', e);
	},

	_getSelectedIds: function () {
		var ids = [];
		var selected = this.getSelectionModel().getSelections();

		for (var i in selected) {
			if (!selected.hasOwnProperty(i)) {
				continue;
			}
			ids.push(selected[i]['id']);
		}

		return ids;
	},

	_doSearch: function (tf, nv, ov) {
		if (tf.name == 'query')
		 this.getStore().baseParams.query = tf.getValue();

		if (tf.name == 'uid')
		 this.getStore().baseParams.uid = tf.getValue();
		this.getBottomToolbar().changePage(1);
		this.refresh();
	},

	_clearSearch: function (btn, e) {
		this.getStore().baseParams.query = '';
		this.getStore().baseParams.uid = '';
		Ext.getCmp(this.config.id + '-search-uid-field').setValue('');
		Ext.getCmp(this.config.id + '-search-field').setValue('');
		this.getBottomToolbar().changePage(1);
		this.refresh();
	},

	onAfterRowMove: function(dt,sri,ri,sels) {
    	    var s = this.getStore();
    	    var sourceRec = s.getAt(sri);
    	    var belowRec = s.getAt(ri);
    	    var total = s.getTotalCount();
	    var upd = {};

    	    sourceRec.set('rank', sri);
	    sourceRec.commit();
	    upd[sourceRec.get('id')] = sri;

        var brec;
        for (var x = (ri - 1); x < total; x++) {
            brec = s.getAt(x);
            if (brec) {
                brec.set('rank', x);
                brec.commit();
		upd[brec.get('id')] = x;
            }
        }
	
	MODx.Ajax.request({
		url: this.config.url,
		params: {
		    action: 'mgr/rank',
		    rank: Ext.util.JSON.encode(upd),
		}
	});

        return true;
    }
});
Ext.reg('fileattach-grid-items', FileAttach.grid.Items);