QoDesk.XmltvWindow = Ext.extend(Ext.app.Module, {
    id: 'xmltv',
    type: 'desktop/xmltv',

    init: function () {
        this.launcher = {
            text: 'xmltv',
            iconCls: 'xmltv-icon',
            handler: this.createWindow,
            scope: this
        };


    },

    createWindow: function () {
        var desktop = this.app.getDesktop();
        var win = desktop.getWindow('grid-win-xmltv');
        var urlver = "imagenes/xmltv/canal/";
        var pathimagenes = "../../../../" + urlver;

        var urlverPrograma = "imagenes/xmltv/programa/";
        var pathimagenesPrograma = "../../../../" + urlverPrograma;

        var urlXmltv = "modules/desktop/xmltv/server/";
        var winWidth = desktop.getWinWidth() / 1.2;
        var winHeight = desktop.getWinHeight() / 1.2;


        var numberField = new Ext.form.NumberField({
            maxValue: 100,
            allowNegative: false,
            allowDecimals: false,
            allowBlank: true,
            minValue: 0
        });

        var textField = new Ext.form.TextField({allowBlank: false});

        var formatoFecha = new Ext.form.DateField({
            format: 'Y-m-d'
        });

        function formatDate(value) {
            return value ? value.dateFormat('Y-m-d') : '';
        }


        //inicio combo activo
        storeACXC = new Ext.data.JsonStore({
            root: 'users',
            fields: ['id', 'nombre'],
            autoLoad: true,
            data: {
                users: [
                    {"id": 1, "nombre": "Si"},
                    {"id": 0, "nombre": "No"}
                ]
            }
        });

        var comboACXC = new Ext.form.ComboBox({
            id: 'comboACXC',
            store: storeACXC,
            valueField: 'id',
            displayField: 'nombre',
            triggerAction: 'all',
            mode: 'local'
        });

        function xmltvActivo(id) {
            var index = storeACXC.find('id', id);
            if (index > -1) {
                var record = storeACXC.getAt(index);
                return record.get('nombre');
            }
        }

        //fin combo activo

        //inicio combo Canales
        storeXmlCan = new Ext.data.JsonStore({
            root: 'data',
            fields: ['id', 'nombre'],
            url: urlXmltv + "crudXmltv.php?operation=canales"
        });
        storeXmlCan.load();

        var comboXmlCan = new Ext.form.ComboBox({
            store: storeXmlCan,
            valueField: 'id',
            displayField: 'nombre',
            triggerAction: 'all',
            mode: 'local'
        });

        function xmltvCanal(id) {
            var index = storeXmlCan.find('id', id);
            if (index > -1) {
                var record = storeXmlCan.getAt(index);
                return record.get('nombre');
            }
        }

        //fin combo Programa

        //inicio combo XmltvFILE
        this.storeXmltvFILE = new Ext.data.JsonStore({
            id: 'storeXmltvFILE',
            root: 'data',
            fields: ['id', 'nombre'],
            url: urlXmltv + "crudXmltv.php?operation=itemsTienda&path=" + pathimagenes + "&urlver=" + urlver
        });
        this.storeXmltvFILE.load();
        storeXmltvFILE = this.storeXmltvFILE;

        var comboXmltvFILE = new Ext.form.ComboBox({
            id: 'comboXmltvFILE',
            store: this.storeXmltvFILE,
            valueField: 'id',
            displayField: 'nombre',
            triggerAction: 'all',
            mode: 'local'
        });

        function xmltvImagenes(id) {
            var index = storeXmltvFILE.find('id', id);
            if (index > -1) {
                var record = storeXmltvFILE.getAt(index);
                return record.get('nombre');
            }
        }

        //fin combo XmltvFILE

        //inicio combo XmltvFILE
        this.storeXmltvFILE2 = new Ext.data.JsonStore({
            id: 'storeXmltvFILE2',
            root: 'data',
            fields: ['id', 'nombre'],
            url: urlXmltv + "crudXmltv.php?operation=itemsTienda&path=" + pathimagenesPrograma + "&urlver=" + urlverPrograma
        });
        this.storeXmltvFILE2.load();
        storeXmltvFILE2 = this.storeXmltvFILE2;
        var comboXmltvFILE2 = new Ext.form.ComboBox({
            id: 'comboXmltvFILE2',
            store: this.storeXmltvFILE2,
            valueField: 'id',
            displayField: 'nombre',
            triggerAction: 'all',
            mode: 'local'
        });

        function xmltvImagenes2(id) {
            var index = storeXmltvFILE2.find('id', id);
            if (index > -1) {
                var record = storeXmltvFILE2.getAt(index);
                return record.get('nombre');
            }
        }

        //fin combo XmltvFILE

        //store combo listado canales
        this.calendarStore = new Ext.data.JsonStore({
            url: urlXmltv + "canalesjson.php",
            storeId: 'calendarStore',
            root: 'data',
            idProperty: 'id',
            //proxy: new Ext.data.MemoryProxy(),
            autoLoad: true,
            fields: [
                {name: 'CalendarId', mapping: 'id', type: 'int'},
                {name: 'Title', mapping: 'title', type: 'string'}
            ],
            sortInfo: {
                field: 'CalendarId',
                direction: 'ASC'
            }
        });
        //fin store combo listado canales

        //item xmltv
        var proxyXmltv = new Ext.data.HttpProxy({
            api: {
                create: urlXmltv + "crudXmltv.php?operation=insert",
                read: urlXmltv + "crudXmltv.php?operation=select",
                update: urlXmltv + "crudXmltv.php?operation=update",
                destroy: urlXmltv + "crudXmltv.php?operation=delete"
            }
        });
        var readerXmltv = new Ext.data.JsonReader({

            successProperty: 'success',
            messageProperty: 'message',
            idProperty: 'id',
            root: 'data',
            fields: [
                {name: 'nombre', allowBlank: false},
                {name: 'descripcion', allowBlank: false},
                {name: 'tag', allowBlank: false},
                {name: 'icono', allowBlank: false},
                {name: 'activo', allowBlank: false},
                {name: 'orden', allowBlank: false}
            ]
        });

        var writerXmltv = new Ext.data.JsonWriter({
            encode: true,
            writeAllFields: true
        });

        this.storeXmltv = new Ext.data.Store({
            id: "id",
            proxy: proxyXmltv,
            reader: readerXmltv,
            writer: writerXmltv,
            autoSave: true
        });
        this.storeXmltv.load();


        this.gridXmltv = new Ext.grid.EditorGridPanel({
            height: winHeight - 144,
            store: this.storeXmltv, columns: [
                new Ext.grid.RowNumberer(),
                {
                    header: 'nombre',
                    dataIndex: 'nombre',
                    sortable: true,
                    width: 80,
                    editor: new Ext.form.TextField({allowBlank: false})
                }
                ,
                {
                    header: 'Descripción',
                    dataIndex: 'descripcion',
                    sortable: true,
                    width: 80,
                    editor: new Ext.form.TextField({allowBlank: false})
                }
                , {
                    header: 'Tag',
                    dataIndex: 'tag',
                    sortable: true,
                    width: 80,
                    editor: new Ext.form.TextField({allowBlank: false})
                }
                , {
                    header: 'Activo',
                    dataIndex: 'activo',
                    sortable: true,
                    width: 30,
                    editor: comboACXC, renderer: xmltvActivo
                }
                , {
                    header: 'Orden',
                    dataIndex: 'orden',
                    sortable: true,
                    width: 30,
                    editor: numberField
                }
                , {
                    header: 'Icono',
                    dataIndex: 'icono',
                    sortable: true,
                    width: 100,
                    editor: comboXmltvFILE, renderer: xmltvImagenes
                }
            ],
            viewConfig: {forceFit: true},
            sm: new Ext.grid.RowSelectionModel({singleSelect: true}),
            border: false,
            stripeRows: true
        });

        // fin xmltv

        //item xmltvPrograma

        var proxyXmltvPrograma = new Ext.data.HttpProxy({
            api: {
                create: urlXmltv + "crudXmltvPrograma.php?operation=insert",
                read: urlXmltv + "crudXmltvPrograma.php?operation=select",
                update: urlXmltv + "crudXmltvPrograma.php?operation=update",
                destroy: urlXmltv + "crudXmltvPrograma.php?operation=delete"
            }
        });

        var readerXmltvPrograma = new Ext.data.JsonReader({
            successProperty: 'success',
            messageProperty: 'message',
            idProperty: 'id',
            root: 'data',
            fields: [
                {name: 'titulo', allowBlank: false},
                {name: 'fecha_fin', type: 'date', dateFormat: 'c', allowBlank: true},
                {name: 'fecha_inicio', type: 'date', dateFormat: 'c', allowBlank: true},
                {name: 'duracion', allowBlank: true},
                {name: 'descripcion', allowBlank: true},
                {name: 'activo', allowBlank: true},
                {name: 'imagen', allowBlank: true},
                {name: 'tipo', allowBlank: true},
                {name: 'id_canal', allowBlank: true}
            ]
        });

        var writerXmltvPrograma = new Ext.data.JsonWriter({
            encode: true,
            writeAllFields: true
        });

        this.storeXmltvPrograma = new Ext.data.Store({
            id: "id",
            proxy: proxyXmltvPrograma,
            reader: readerXmltvPrograma,
            writer: writerXmltvPrograma,
            autoSave: true
        });
        this.storeXmltvPrograma.load();


        this.gridXmltvPrograma = new Ext.grid.EditorGridPanel({
            height: winHeight - 144,
            store: this.storeXmltvPrograma, columns: [
                new Ext.grid.RowNumberer(),
                {
                    header: 'Nombre',
                    dataIndex: 'titulo',
                    sortable: true,
                    width: 40,
                    editor: textField
                }, {
                    header: 'Descripción',
                    dataIndex: 'descripcion',
                    sortable: true,
                    width: 120,
                    editor: textField
                },
                {
                    header: 'Fecha Inicio',
                    dataIndex: 'fecha_inicio',
                    sortable: true,
                    width: 50,
                    editor: formatoFecha,
                    renderer: formatDate
                },
                {
                    header: 'Fecha Fin',
                    dataIndex: 'fecha_fin',
                    sortable: true,
                    width: 50,
                    editor: formatoFecha,
                    renderer: formatDate
                },
                {
                    header: 'Duración (m)',
                    dataIndex: 'duracion',
                    sortable: true,
                    width: 30,
                    editor: numberField
                },

                {
                    header: 'Activo',
                    dataIndex: 'activo',
                    sortable: true,
                    width: 30,
                    editor: comboACXC, renderer: xmltvActivo
                },
                {
                    header: 'Canal',
                    dataIndex: 'id_canal',
                    sortable: true,
                    width: 30,
                    editor: comboXmlCan, renderer: xmltvCanal
                },

                {
                    header: 'Imagen',
                    dataIndex: 'imagen',
                    sortable: true,
                    width: 50,
                    editor: comboXmltvFILE2, renderer: xmltvImagenes2
                }
            ],
            viewConfig: {forceFit: true},
            sm: new Ext.grid.RowSelectionModel({singleSelect: false}),
            border: false,
            stripeRows: true
        });

        // fin xmltvPrograma


        //item xmltvMensajes

        var proxyXmltvMensajes = new Ext.data.HttpProxy({
            api: {
                create: urlXmltv + "crudXmltvMensajes.php?operation=insert",
                read: urlXmltv + "crudXmltvMensajes.php?operation=select",
                update: urlXmltv + "crudXmltvMensajes.php?operation=update",
                destroy: urlXmltv + "crudXmltvMensajes.php?operation=delete"
            }
        });

        var readerXmltvMensajes = new Ext.data.JsonReader({
            successProperty: 'success',
            messageProperty: 'message',
            idProperty: 'id',
            root: 'data',
            fields: [
                {name: 'body', allowBlank: true},
                {name: 'l', allowBlank: true},
                {name: 'tag', allowBlank: true},
                {name: 'richpage', allowBlank: true},
                {name: 'activo', allowBlank: false},
                {name: 'creado', type: 'date', dateFormat: 'c', allowBlank: true},
            ]
        });

        var writerXmltvMensajes = new Ext.data.JsonWriter({
            encode: true,
            writeAllFields: true
        });

        this.storeXmltvMensajes = new Ext.data.Store({
            id: "id",
            proxy: proxyXmltvMensajes,
            reader: readerXmltvMensajes,
            writer: writerXmltvMensajes,
            autoSave: true
        });
        this.storeXmltvMensajes.load();


        this.gridXmltvMensajes = new Ext.grid.EditorGridPanel({
            height: winHeight - 144,
            store: this.storeXmltvMensajes, columns: [
                new Ext.grid.RowNumberer(),
                {
                    header: 'Body',
                    dataIndex: 'body',
                    sortable: true,
                    width: 120,
                    editor: new Ext.form.TextField({allowBlank: true})
                },
                {
                    header: 'l',
                    dataIndex: 'l',
                    sortable: true,
                    width: 120,
                    editor: new Ext.form.TextField({allowBlank: true})
                },
                {
                    header: 'tag',
                    dataIndex: 'tag',
                    sortable: true,
                    width: 50,
                    editor: new Ext.form.TextField({allowBlank: true})
                },
                {
                    header: 'richpage id',
                    dataIndex: 'richpage',
                    sortable: true,
                    width: 80,
                    editor: new Ext.form.TextField({allowBlank: true})
                },
                {
                    header: 'Activo',
                    dataIndex: 'activo',
                    sortable: true,
                    width: 80,
                    editor: comboACXC, renderer: xmltvActivo
                },
                {header: 'Creado', dataIndex: 'creado', sortable: true, width: 60, renderer: formatDate}
            ],
            viewConfig: {forceFit: true},
            sm: new Ext.grid.RowSelectionModel({singleSelect: true}),
            border: false,
            stripeRows: true
        });

        // fin xmltvMensajes

        if (!win) {
            win = desktop.createWindow({
                id: 'grid-win-xmltv',
                title: 'Configuración Xmltv',
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
                            title: 'Canales ',
                            closable: true,
                            tbar: [
                                {text: 'Nuevo', scope: this, handler: this.addxmltvCanal, iconCls: 'add-icon'},
                                '-',
                                {text: "Eliminar", scope: this, handler: this.deletexmltvCanal, iconCls: 'delete-icon'},
                                '-', {
                                    iconCls: 'demo-grid-add',
                                    handler: this.requestXmltvData,
                                    scope: this,
                                    text: 'Recargar Datos', iconCls: 'x-tbar-loading'
                                }/*,'-', {
                                 iconCls: 'demo-grid-add',
                                 handler: this.enviarMensajeXmltv,
                                 scope: this,
                                 text: 'Enviar Mensaje', iconCls: 'x-tbar-loading'
                                 }*/
                                , '->',
                                {
                                    xtype: 'form',
                                    fileUpload: true,
                                    width: 400,
                                    frame: true,
                                    autoHeight: true,
                                    defaults: {
                                        anchor: '95%',
                                        allowBlank: false
                                    },

                                    id: "fp",
                                    items: [
                                        {
                                            xtype: 'fileuploadfield',
                                            id: 'form-file-xmltv',
                                            emptyText: 'Seleccione imagen a subir',
                                            fieldLabel: 'Imagen',
                                            name: 'photo-path',
                                            regex: /^.*.(jpg|JPG|jpeg|JPEG|gif|GIF|png|PNG)$/,
                                            regexText: 'Solo imagenes ',
                                            buttonText: '',
                                            buttonCfg: {
                                                iconCls: 'ux-start-menu-submenu'
                                            }
                                        }
                                    ], buttons: [
                                    {
                                        text: 'Subir', iconCls: 'save-icon-xmltv',
                                        handler: function () {
                                            if (Ext.getCmp('fp').getForm().isValid()) {
                                                Ext.getCmp('fp').getForm().submit({
                                                    params: {url: pathimagenes},
                                                    url: urlXmltv + 'file-upload.php',
                                                    waitMsg: 'Subiendo Imagen...',
                                                    success: function (fp, o) {
                                                        this.storeXmltvFILE.load();
                                                    }
                                                });
                                            }
                                        }
                                    }
                                ]
                                }


                            ],
                            items: this.gridXmltv
                        },
                        {
                            autoScroll: true,
                            title: 'Programas',
                            closable: true,
                            tbar: [
                                {text: 'Nuevo', scope: this, handler: this.addxmltvPrograma, iconCls: 'add-icon'},
                                '-',
                                {
                                    text: "Eliminar",
                                    scope: this,
                                    handler: this.deletexmltvPrograma,
                                    iconCls: 'delete-icon'
                                },
                                '-', {
                                    iconCls: 'demo-grid-add',
                                    handler: this.requestXmltvProgramaData,
                                    scope: this,
                                    text: 'Recargar Datos', iconCls: 'x-tbar-loading'
                                }

                                , '->',
                                {
                                    xtype: 'form',
                                    fileUpload: true,
                                    width: 400,
                                    frame: true,
                                    autoHeight: true,
                                    defaults: {
                                        anchor: '95%',
                                        allowBlank: false

                                    },

                                    id: "fp2",
                                    items: [
                                        {
                                            xtype: 'fileuploadfield',
                                            id: 'form-file2-xmltv',
                                            emptyText: 'Seleccione imagen a subir',
                                            fieldLabel: 'Imagen',
                                            name: 'photo-path',
                                            regex: /^.*.(jpg|JPG|jpeg|JPEG|gif|GIF|png|PNG)$/,
                                            regexText: 'Solo imagenes ',
                                            buttonText: '',
                                            buttonCfg: {
                                                iconCls: 'ux-start-menu-submenu'
                                            }
                                        }
                                    ], buttons: [
                                    {
                                        text: 'Subir',
                                        iconCls: 'save-icon-xmltv',
                                        handler: function () {
                                            if (Ext.getCmp('fp2').getForm().isValid()) {
                                                Ext.getCmp('fp2').getForm().submit({
                                                    params: {url: pathimagenesPrograma},
                                                    url: urlXmltv + 'file-upload.php',
                                                    waitMsg: 'Subiendo Imagen...',
                                                    success: function (fp, o) {
                                                        storeXmltvFILE2.load();
                                                    }
                                                });
                                            }
                                        }
                                    }
                                ]
                                }


                            ],
                            items: this.gridXmltvPrograma
                        }
                        ,
                        {
                            autoScroll: true,
                            title: 'Mensajes Xmltv',
                            closable: true,
                            tbar: [
                                {
                                    text: 'Nuevo',
                                    scope: this,
                                    handler: this.addxmltvPrograma,
                                    iconCls: 'add-icon',
                                    disabled: true
                                },
                                '-',
                                {
                                    text: "Eliminar",
                                    scope: this,
                                    handler: this.deletexmltvPrograma,
                                    iconCls: 'delete-icon',
                                    disabled: true
                                },
                                '-', {
                                    iconCls: 'demo-grid-add',
                                    handler: this.requestXmltvMensajesData,
                                    scope: this,
                                    text: 'Recargar Datos', iconCls: 'x-tbar-loading'
                                }
                            ],
                            items: this.gridXmltvMensajes
                        }
                    ]
                })
            });
        }
        win.show();
    },
    deletexmltvCanal: function () {
        Ext.Msg.show({
            title: 'Confirmación',
            msg: 'Está seguro de querer borrar?',
            scope: this,
            buttons: Ext.Msg.YESNO,
            fn: function (btn) {
                if (btn == 'yes') {
                    var rows = this.gridXmltv.getSelectionModel().getSelections();
                    if (rows.length === 0) {
                        return false;
                    }
                    this.storeXmltv.remove(rows);
                }
            }
        });
    },
    addxmltvCanal: function () {
        var xmltv = new this.storeXmltv.recordType({
            nombre: '',
            tag: '',
            descripcion: '',
            icono: '',
            activo: '0',
            orden: '0'
        });
        this.gridXmltv.stopEditing();
        this.storeXmltv.insert(0, xmltv);
        this.gridXmltv.startEditing(0, 1);
    },
    requestXmltvData: function () {
        this.storeXmltv.load();
        storeXmlCan.load();
        this.storeXmltvFILE.load();
    },
    deletexmltvPrograma: function () {
        Ext.Msg.show({
            title: 'Confirmación',
            msg: 'Está seguro de querer borrar?',
            scope: this,
            buttons: Ext.Msg.YESNO,
            fn: function (btn) {
                if (btn == 'yes') {
                    var rows = this.gridXmltvPrograma.getSelectionModel().getSelections();
                    if (rows.length === 0) {
                        return false;
                    }
                    this.storeXmltvPrograma.remove(rows);
                }
            }
        });
    },
    addxmltvPrograma: function () {
        var xmltvPrograma = new this.storeXmltvPrograma.recordType({
            nombre: '',
            icono: '',
            orden2: '0'
        });
        this.gridXmltvPrograma.stopEditing();
        this.storeXmltvPrograma.insert(0, xmltvPrograma);
        this.gridXmltvPrograma.startEditing(0, 1);


    },
    requestXmltvProgramaData: function () {
        this.storeXmltvPrograma.load();
        storeXmlCan.load();
        this.storeXmltvFILE.load();
    }
    , enviarMensajeXmltv: function () {
        var index = this.gridXmltv.getSelectionModel().getSelections();
        var record = index[0];
        if (!record || record.phantom === true) {
            return;
        }

        this.gridEditor.show(record, function (groupIds) {
            this.showMask('Actualizando Grupos...');
            Ext.Ajax.request({
                callback: function (options, success, response) {
                    this.hideMask();
                    if (success) {
                        var decoded = Ext.decode(response.responseText);
                        if (decoded.success === true) {

                        } else {
                            Ext.MessageBox.alert('Warning', 'Error occured on the server!');
                        }
                    } else {
                        Ext.MessageBox.alert('Warning', 'Lost connection to the server!');
                    }
                }
                , params: {
                    method: 'editMembersGroups'
                    , moduleId: this.ownerModule.id
                    , memberId: record.data.id
                    , groupIds: Ext.encode(groupIds)
                }
                , scope: this
                , url: this.ownerModule.app.connection
            });
        }, this);
    },
    requestXmltvMensajesData: function () {
        this.storeXmltvMensajes.load();
    }

});
