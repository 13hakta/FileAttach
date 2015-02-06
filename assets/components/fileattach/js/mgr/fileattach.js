var FileAttach = function (config) {
	config = config || {};
	FileAttach.superclass.constructor.call(this, config);
};
Ext.extend(FileAttach, Ext.Component, {
	page: {}, window: {}, grid: {}, tree: {}, panel: {}, combo: {}, config: {}, view: {}, utils: {}
});
Ext.reg('fileattach', FileAttach);

FileAttach = new FileAttach();