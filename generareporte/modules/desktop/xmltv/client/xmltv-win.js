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
        var pathimagenes = "../../../../../../imagenes/xmltvslisto/promociones-14-01/";
        var urlver = "imagenes/xmltvslisto/promociones-14-01/";

        //inicio combo Xmltv
        this.storeXmltv = new Ext.data.JsonStore({
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
        }
        //fin combo Xmltv
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
        // form canal
       /* this.formXmltvCanalDetalle = new Ext.FormPanel({
            id: 'formXmltvCanalDetalle',
            items: [
                {
                    collapsible: true,
                    id: 'formcabeceraxmltv',
                    collapsedTitle: true,
                    titleCollapse: true,
                    split: true,
                    flex: 1,
                    autoScroll: true,
                    title: 'Detalle Canal',
                    layout: 'column', items: this.gridXmltvCanal
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
                    title: 'Detalle Xmltv',
                    bodyStyle: 'padding:0; background: #DFE8F6',
                    layout: 'column',
                    tbar: [
                        {
                            text: 'Grabar',
                            scope: this,
                            handler: this.grabarxmltv,
                            iconCls: 'save-icon',
                            disabled: true,
                            id: 'tb_grabarxmltv'
                        }
                    ],
                    items: [
                        {
                            frame: true,
                            columnWidth: 1,
                            layout: 'form',
                            id: 'formXmltvCanal',
                            items: [
                                {
                                    xtype: 'textfield',
                                    fieldLabel: 'Nombre',
                                    name: 'nombre',
                                    anchor: '95%',
                                    readOnly: false
                                },
                                {
                                    xtype: 'textfield',
                                    fieldLabel: 'Descripción',
                                    name: 'descripcion',
                                    anchor: '95%',
                                    readOnly: false
                                },
                                {
                                    xtype: 'textfield',
                                    fieldLabel: 'Icono',
                                    name: 'icono',
                                    anchor: '95%',
                                    readOnly: false
                                },
                                {xtype: 'textfield', fieldLabel: 'Id', name: 'id', anchor: '95%', readOnly: true}
                            ]
                        }
                    ]

                }
            ]
        });*/
        // fin form canal

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
                                    handler: this.requestGridData,
                                    scope: this,
                                    text: 'Recargar Datos',
                                    tooltip: 'Recargar datos en la grilla'
                                }

                            ],
                            items: this.gridXmltvCanal
                        }
                    ]
                })




            });
        }
        win.show();

        /*function cargaDetalle(xmltv, forma) {
            forma.getForm().load({
                url: 'modules/desktop/xmltv/server/crudCanal.php?operation=selectForm',
                params: {
                    id: xmltv
                }
            });
        };
        this.gridXmltvCanal.on('rowclick', function (grid, rowIndex) {
            this.record = this.gridXmltvCanal.getStore().getAt(rowIndex);
            this.idXmltvCanalRecuperada = this.record.id;

            /!*cargar el formulario*!/
            cargaDetalle(this.idXmltvCanalRecuperada, this.formXmltvCanalDetalle);
            Ext.getCmp('tb_grabarxmltv').setDisabled(false);
        }, this);*/
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
    }, requestGridData: function () {
        this.storeXmltvCanal.load();
    }/*,
    grabarxmltv: function () {
        Ext.Msg.show({
            title: 'Advertencia',
            msg: 'Desea Guardar los cambios.<br>¿Desea continuar?',
            scope: this,
            icon: Ext.Msg.WARNING,
            buttons: Ext.Msg.YESNO,
            fn: function (btn) {
                if (btn == 'yes') {
                    var myForm = Ext.getCmp('formXmltvCanalDetalle').getForm();
                    myForm.submit({
                        url: 'modules/desktop/xmltv/server/crudXmltvCanal.php?operation=updateForm',
                        method: 'POST',
                        fileUpload: true,
                        submitEmptyText: false,
                        // waitMsg : 'Saving data',
                        success: function (form, action) {
                            storeXmltvCanal.load();
                        }
                    });
                }
            }
        });
    }*/

});
