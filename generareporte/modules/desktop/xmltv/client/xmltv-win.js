QoDesk.XmltvWindow = Ext.extend(Ext.app.Module, {
    id: 'xmltv',
    type: 'desktop/xmltv',

    init: function () {
        this.launcher = {
            text: 'Xmltv',
            iconCls: 'xmltv-icon',
            handler: this.createWindow,
            scope: this
        }
    },

    createWindow: function () {
        var desktop = this.app.getDesktop();
        var win = desktop.getWindow('grid-win-xmltv');

        var urlXmltv = "modules/desktop/xmltv/server/";

        //inicio combo Xmltv
/*        this.storeXmltv = new Ext.data.JsonStore({
            id: 'storeXmltv',
            root: 'data',
            fields: ['id', 'nombre'],
            url: urlXmltv + "crudXmltv.php?operation=itemsXmltv&path=" + pathimagenes + "&urlver=" + urlver
        });
        this.storeXmltv.load();
        storeXmltv = this.storeXmltv;


        var comboXmltv = new Ext.form.ComboBox({
            id: 'comboXmltv',
            store: this.storeXmltv,
            valueField: 'id',
            displayField: 'nombre',
            triggerAction: 'all',
            mode: 'local'
        });

        function xmltvImagenes(id) {
            var index = this.storeXmltv.find('id', id);
            if (index > -1) {
                var record = this.storeXmltv.getAt(index);
                return record.get('nombre');
            }
        }*/

        //fin combo Xmltv



        //inicio combo Canal
        this.storeComboCanal = new Ext.data.JsonStore({
            id: 'storeComboCanal',
            root: 'data',
            fields: ['id', 'nombre'],
            url: urlXmltv + "crudPrograma.php?operation=itemsCanal"
        });
        this.storeComboCanal.load();
        storeComboCanal = this.storeComboCanal;

        var comboCanal = new Ext.form.ComboBox({
            id: 'comboCanal',
            store: this.storeComboCanal,
            valueField: 'id',
            displayField: 'nombre',
            triggerAction: 'all',
            mode: 'local'
        });

        function programaImagenes(id) {
            var index = this.storeComboCanal.find('id', id);
            if (index > -1) {
                var record = this.storeComboCanal.getAt(index);
                return record.get('nombre');
            }
        }
        //fin combo Canal

        // grid Canal
        var proxyXmltvCanal = new Ext.data.HttpProxy({
            api: {
                create: urlXmltv + "crudCanal.php?operation=insert",
                read: urlXmltv + "crudCanal.php?operation=select",
                update: urlXmltv + "crudCanal.php?operation=update",
                destroy: urlXmltv + "crudCanal.php?operation=delete"
            }
        });

        var readerXmltvCanal = new Ext.data.JsonReader({
            totalProperty: 'total',
            successProperty: 'success',
            messageProperty: 'message',
            idProperty: 'id',
            root: 'data',
            fields: [
                {name: 'nombre', allowBlank: false},
                {name: 'descripcion', allowBlank: false},
                {name: 'icono', allowBlank: false}
            ]
        });
        var writerXmltvCanal = new Ext.data.JsonWriter({
            encode: true,
            writeAllFields: true
        });
        this.storeXmltvCanal = new Ext.data.Store({
            id: "id",
            proxy: proxyXmltvCanal,
            reader: readerXmltvCanal,
            writer: writerXmltvCanal,
            autoSave: true
        });
        this.storeXmltvCanal.load();
        storeXmltvCanal = this.storeXmltvCanal;

        var textField = new Ext.form.TextField({allowBlank: false});

        function formatDate(value) {
            return value ? value.dateFormat('Y-m-d') : '';
        }

        var formatoFechaMax = new Ext.form.DateField({
            format: 'Y-m-d'
        });

        this.gridXmltvCanal = new Ext.grid.EditorGridPanel({
            height: 300,
            store: this.storeXmltvCanal, columns: [
                {
                    header: 'Nombre',
                    dataIndex: 'nombre',
                    sortable: true,
                    width: 100,
                    editor: new Ext.form.TextField({allowBlank: false})
                },
                {
                    header: 'Descripción',
                    dataIndex: 'descripcion',
                    sortable: true,
                    width: 300,
                    editor: new Ext.form.TextField({allowBlank: false})
                },
                {
                    header: 'Icono',
                    dataIndex: 'icono',
                    sortable: true,
                    width: 200,
                    editor: new Ext.form.TextField({allowBlank: false})
                }
            ],
            viewConfig: {forceFit: true},
            sm: new Ext.grid.RowSelectionModel({singleSelect: false}),
            border: false,
            stripeRows: true
        });
        // fin grid canal
        // grid Programa
        var proxyPrograma = new Ext.data.HttpProxy({
            api: {
                create: urlXmltv + "crudPrograma.php?operation=insert",
                read: urlXmltv + "crudPrograma.php?operation=select",
                update: urlXmltv + "crudPrograma.php?operation=update",
                destroy: urlXmltv + "crudPrograma.php?operation=delete"
            }
        });

        var readerPrograma = new Ext.data.JsonReader({
            totalProperty: 'total',
            successProperty: 'success',
            messageProperty: 'message',
            idProperty: 'id',
            root: 'data',
            fields: [
                {name: 'titulo', allowBlank: false},
                {name: 'imagen', allowBlank: false},
                {name: 'subtitulos', allowBlank: false},
                {name: 'categoria', allowBlank: false},
                {name: 'inicio', allowBlank: false, type: 'date', dateFormat: 'c'},
                {name: 'audio', allowBlank: false},
                {name: 'descripcion', allowBlank: false},
                {name: 'subtititulo', allowBlank: false},
                {name: 'id_canal', allowBlank: false},
                {name: 'episodio', allowBlank: false},
                {name: 'fecha', allowBlank: false, type: 'date', dateFormat: 'c'}
            ]
        });
        var writerPrograma = new Ext.data.JsonWriter({
            encode: true,
            writeAllFields: true
        });
        this.storePrograma = new Ext.data.Store({
            id: "id",
            proxy: proxyPrograma,
            reader: readerPrograma,
            writer: writerPrograma,
            autoSave: true
        });
        this.storePrograma.load();
        storePrograma = this.storePrograma;

        var textField = new Ext.form.TextField({allowBlank: false});

        function formatDate(value) {
            return value ? value.dateFormat('Y-m-d') : '';
        }

        var formatoFechaMax = new Ext.form.DateField({
            format: 'Y-m-d'
        });

        var formatoTexto = new Ext.form.TextField({allowBlank: false})

        this.gridPrograma = new Ext.grid.EditorGridPanel({
            height: 300,
            store: this.storePrograma, columns: [
                { header: 'Título', dataIndex: 'titulo', sortable: true, width: 100, editor: formatoTexto }
                ,{ header: 'Imagen', dataIndex: 'imagen', sortable: true, width: 100, editor: formatoTexto }
                ,{ header: 'Subtítulos', dataIndex: 'subtitulos', sortable: true, width: 100, editor: formatoTexto }
                ,{ header: 'Categoría', dataIndex: 'categoria', sortable: true, width: 100, editor: formatoTexto }
                ,{ header: 'Inicio', dataIndex: 'inicio', sortable: true, width: 100, editor: formatoTexto }
                ,{ header: 'Audio', dataIndex: 'audio', sortable: true, width: 100, editor: formatoTexto }
                ,{ header: 'Descripción', dataIndex: 'descripcion', sortable: true, width: 100, editor: formatoTexto }
                ,{ header: 'Subtítulo', dataIndex: 'subtititulo', sortable: true, width: 100, editor: formatoTexto }
                ,{ header: 'Canal', dataIndex: 'id_canal', sortable: true, width: 100, editor: formatoTexto }
                ,{ header: 'Episodio', dataIndex: 'episodio', sortable: true, width: 100, editor: formatoTexto }
                ,{ header: 'Fecha', dataIndex: 'fecha', sortable: true, width: 100, editor: formatoTexto }
            ],
            viewConfig: {forceFit: true},
            sm: new Ext.grid.RowSelectionModel({singleSelect: false}),
            border: false,
            stripeRows: true
        });
        // fin grid programa
        // form programa
        this.formProgramaDetalle = new Ext.FormPanel({
            id: 'formProgramaDetalle',

            items: [
                {
                    collapsible: true,
                    id: 'formcabeceraprograma',
                    collapsedTitle: true,
                    titleCollapse: true,
                    split: true,
                    flex: 1,
                    autoScroll: true,
                    title: 'Listado Programaes',
                    layout: 'column', items: this.gridPrograma
                },
                {
                    collapsible: true,
                    collapsedTitle: true,
                    titleCollapse: true,
                    split: true,
                    flex: 2,
                    height: 'auto',
                    autoScroll: true,
                    labelAlign: 'left',
                    title: 'Detalle Programa',
                    bodyStyle: 'padding:0; background: #DFE8F6',
                    layout: 'column',
                    tbar: [
                        {
                            text: 'Grabar',
                            scope: this,
                            handler: this.grabarprograma,
                            iconCls: 'save-icon',
                            disabled: true,
                            id: 'tb_grabarprograma'
                        }
                    ],
                    items: [
                        {
                            frame: true,
                            columnWidth: 1,
                            layout: 'form',
                            id: 'formPrograma',
                            items: [
                                {  xtype: 'textfield', fieldLabel: 'Id', name: 'id', anchor: '95%', readOnly: true },
                                {  xtype: 'textfield', fieldLabel: 'Título', name: 'titulo', anchor: '95%', readOnly: false },
                                {  xtype: 'textfield', fieldLabel: 'Imagen', name: 'imagen', anchor: '95%', readOnly: false },
                                {  xtype: 'textfield', fieldLabel: 'Subtítulos', name: 'subtitulos', anchor: '95%', readOnly: false },
                                {  xtype: 'textfield', fieldLabel: 'Categoría', name: 'categoria', anchor: '95%', readOnly: false },
                                {  xtype: 'textfield', fieldLabel: 'Inicio', name: 'inicio', anchor: '95%', readOnly: false },
                                {  xtype: 'textfield', fieldLabel: 'Audio', name: 'audio', anchor: '95%', readOnly: false },
                                {  xtype: 'textfield', fieldLabel: 'Descripción', name: 'descripcion', anchor: '95%', readOnly: false },
                                {  xtype: 'textfield', fieldLabel: 'Subtitulo', name: 'subtititulo', anchor: '95%', readOnly: false },
                                {  xtype: 'textfield', fieldLabel: 'Canal', name: 'id_canal', anchor: '95%', readOnly: false },
                                {  xtype: 'textfield', fieldLabel: 'Episodio', name: 'episodio', anchor: '95%', readOnly: false },
                                {  xtype: 'textfield', fieldLabel: 'Fecha', name: 'fecha', anchor: '95%', readOnly: false }
                            ]
                        }
                    ]

                }
            ]
        });
        // fin formprograma


        var desktop = this.app.getDesktop();
        var win = desktop.getWindow('layout-win');


        if (!win) {
            var winWidth = desktop.getWinWidth() / 1.1;
            var winHeight = desktop.getWinHeight() / 1.1;


            win = desktop.createWindow({
                id: 'grid-win-xmltv',
                title: 'Xmltv',
                width: winWidth,
                height: winHeight,
                iconCls: 'xmltv-icon',
                shim: false,
                animCollapse: false,
                constrainHeader: true,
                layout: 'fit',
                items: new Ext.TabPanel({
                    activeTab: 0,
                    border: false,
                    items: [
                        {
                            autoScroll: true,
                            title: 'Canales',
                            iconCls: 'xmltv-icon',
                            closable: true,
                            tbar: [
                                {text: 'Nuevo', scope: this, handler: this.addxmltvcanal, iconCls: 'save-icon'},
                                '-',
                                {text: "Eliminar", scope: this, handler: this.deletexmltvcanal, iconCls: 'delete-icon'},
                                '-',
                                {
                                    iconCls: 'demo-grid-add',
                                    handler: this.reloadxmltvcanal,
                                    scope: this,
                                    text: 'Recargar Datos',
                                    tooltip: 'Recargar datos en la grilla'
                                }

                            ],
                            items: this.gridXmltvCanal
                        },
                        {
                            autoScroll: true,
                            title: 'Programas',
                            iconCls: 'xmltv-icon',
                            closable: true,
                            tbar: [
                                {text: 'Nuevo', scope: this, handler: this.addprograma, iconCls: 'save-icon'},
                                '-',
                                {text: "Eliminar", scope: this, handler: this.deleteprograma, iconCls: 'delete-icon'},
                                '-',
                                {
                                    iconCls: 'demo-grid-add',
                                    handler: this.requestGridData,
                                    scope: this,
                                    text: 'Recargar Datos',
                                    tooltip: 'Recargar datos en la grilla'
                                }

                            ],
                            items: this.formProgramaDetalle
                        }
                    ]
                })
            });
        }
        win.show();
        function cargaDetalle(programa, forma) {
            forma.getForm().load({
                url: 'modules/desktop/programa/server/crudPrograma.php?operation=selectForm',
                params: {
                    id: programa
                }
            });
        };
        this.gridPrograma.on('rowclick', function (grid, rowIndex) {
            this.record = this.gridPrograma.getStore().getAt(rowIndex);
            this.idProgramaRecuperada = this.record.id;

            /*cargar el formulario*/
            cargaDetalle(this.idProgramaRecuperada, this.formProgramaDetalle);
            Ext.getCmp('tb_grabarprograma').setDisabled(false);
        }, this);

    }, deletexmltvcanal: function () {
        Ext.Msg.show({
            title: 'Confirmación',
            msg: 'Está seguro de querer borrar?',
            scope: this,
            buttons: Ext.Msg.YESNO,
            fn: function (btn) {
                if (btn == 'yes') {
                    var rows = this.gridXmltvCanal.getSelectionModel().getSelections();
                    if (rows.length === 0) {
                        return false;
                    }
                    this.storeXmltvCanal.remove(rows);
                }
            }
        });
    }, addxmltvcanal: function () {
        var xmltv = new this.storeXmltvCanal.recordType({
            nombre: '',
            descripcion: '',
            icono: ''
        });
        this.gridXmltvCanal.stopEditing();
        this.storeXmltvCanal.insert(0, xmltv);
        this.gridXmltvCanal.startEditing(0, 1);
    }, reloadxmltvcanal: function () {
        this.storeXmltvCanal.load();
    }
    , deleteprograma: function () {
    Ext.Msg.show({
        title: 'Confirmación',
        msg: 'Está seguro de querer borrar?',
        scope: this,
        buttons: Ext.Msg.YESNO,
        fn: function (btn) {
            if (btn == 'yes') {
                var rows = this.gridPrograma.getSelectionModel().getSelections();
                if (rows.length === 0) {
                    return false;
                }
                this.storePrograma.remove(rows);
            }
        }
    });
},
    addprograma: function () {
    var programa = new this.storePrograma.recordType({
        nombre: '',
        descripcion: '',
        icono: ''
    });
    this.gridPrograma.stopEditing();
    this.storePrograma.insert(0, programa);
    this.gridPrograma.startEditing(0, 1);
}, requestGridData: function () {
    this.storePrograma.load();
}
, grabarprograma: function () {
    Ext.Msg.show({
        title: 'Advertencia',
        msg: 'Desea Guardar los cambios.<br>¿Desea continuar?',
        scope: this,
        icon: Ext.Msg.WARNING,
        buttons: Ext.Msg.YESNO,
        fn: function (btn) {
            if (btn == 'yes') {
                var myForm = Ext.getCmp('formProgramaDetalle').getForm();
                myForm.submit({
                    url: 'modules/desktop/programa/server/crudPrograma.php?operation=updateForm',
                    method: 'POST',
                    fileUpload: true,
                    submitEmptyText: false,
                    // waitMsg : 'Saving data',
                    success: function (form, action) {
                        storePrograma.load();
                    }
                });
            }
        }
    });
}




});
