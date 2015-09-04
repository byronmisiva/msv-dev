QoDesk.KiiconnectWindow = Ext.extend(Ext.app.Module, {
    id: 'kiiconnect',
    type: 'desktop/kiiconnect',

    init: function () {
        this.launcher = {
            text: 'Kiiconnect',
            iconCls: 'kiiconnect-icon',
            handler: this.createWindow,
            scope: this
        }
    },

    createWindow: function () {
        var desktop = this.app.getDesktop();
        var win = desktop.getWindow('grid-win-kiiconnect');
        var urlKiiconnect = "modules/desktop/kiiconnect/server/";
        var winWidth = desktop.getWinWidth() / 1.2;
        var winHeight = desktop.getWinHeight() / 1.2;

        var textField = new Ext.form.TextField({allowBlank: false});

        function formatDate(value) {
            return value ? value.dateFormat('Y-m-d') : '';
        }

        var formatoFechaMax = new Ext.form.DateField({
            format: 'Y-m-d',
            background: '#0000ff'
        });
        /*
         //inicio combo sexo
         this.storeOFSE = new Ext.data.JsonStore({
         root: 'users',
         fields: [ 'id', 'nombre' ],
         autoLoad: true,
         data: { users: [
         { "id": 'I', "nombre":"Indistinto"},
         { "id": 'M', "nombre":"Mujer"},
         { "id": 'H', "nombre":"Hombre"}

         ]}
         });

         var comboOFSE = new Ext.form.ComboBox({
         id: 'comboOFSE',
         store: this.storeOFSE,
         valueField: 'id',
         displayField: 'nombre',
         triggerAction: 'all',
         mode: 'local'
         });

         function kiiconnectSexo(id) {
         var index = this.storeOFSE.find('id', id);
         if (index > -1) {
         var record = this.storeOFSE.getAt(index);
         return record.get('nombre');
         }
         }

         //fin combo sexo
         //inicio combo activo

         this.storeOFAC = new Ext.data.JsonStore({
         root: 'users',
         fields: [ 'id', 'nombre' ],
         autoLoad: true,
         data: { users: [
         { "id": 1, "nombre":"Si"},
         { "id": 0, "nombre":"No"}

         ]}
         });

         var comboOFAC = new Ext.form.ComboBox({
         id: 'comboOFAC',
         store: this.storeOFAC,
         valueField: 'id',
         displayField: 'nombre',
         triggerAction: 'all',
         mode: 'local'
         });

         function kiiconnectActivo(id) {
         var index = this.storeOFAC.find('id', id);
         if (index > -1) {
         var record = this.storeOFAC.getAt(index);
         return record.get('nombre');
         }
         }

         //fin combo activo

         //inicio combo cargo
         this.storeFA = new Ext.data.JsonStore({
         id: 'storeFA',
         root: 'data',
         fields: ['id', 'nombre'],
         url: 'modules/common/combos/combos.php?tipo=cargo'
         });
         this.storeFA.load();

         var comboEJFA = new Ext.form.ComboBox({
         id: 'comboEJFA',
         store: this.storeFA,
         valueField: 'id',
         displayField: 'nombre',
         triggerAction: 'all',
         mode: 'local'
         });

         function kiiconnectCargo(id) {
         var index = this.storeFA.find('id', id);
         if (index > -1) {
         var record = this.storeFA.getAt(index);
         return record.get('nombre');
         }
         }

         //fin combo cargo

         //inicio combo campo
         this.storePrFa = new Ext.data.JsonStore({
         root: 'data',
         fields: ['id', 'nombre'],
         url: 'modules/common/combos/combos.php?tipo=campo'
         });
         this.storePrFa.load();

         var comboPrFa = new Ext.form.ComboBox({
         store: this.storePrFa,
         valueField: 'id',
         displayField: 'nombre',
         triggerAction: 'all',
         mode: 'local'
         });

         function kiiconnectCampo(id) {
         var index = this.storePrFa.find('id', id);
         if (index > -1) {
         var record = this.storePrFa.getAt(index);
         return record.get('nombre');
         }
         }

         //fin combo campo
         */

        //Kiiconnect tab
        var proxyKiiconnectKaraoke = new Ext.data.HttpProxy({
            api: {
                create: urlKiiconnect + "crudKiiconnect.php?operation=insert",
                read: urlKiiconnect + "crudKiiconnect.php?operation=selectKiiconnect",
                update: urlKiiconnect + "crudKiiconnect.php?operation=updateKiiconnect",
                destroy: urlKiiconnect + "crudKiiconnect.php?operation=delete"
            }
        });

        var readerKiiconnectKaraoke = new Ext.data.JsonReader({
            totalProperty: 'total',
            successProperty: 'success',
            messageProperty: 'message',
            idProperty: 'id',
            root: 'data',
            fields: [
                {name: 'id', allowBlank: false},
                {name: 'nombre', allowBlank: false},
                {name: 'descripcion', allowBlank: false},
                {name: 'slogan', allowBlank: false},
                {name: 'icono', allowBlank: false},
                {name: 'link', allowBlank: false},
                {name: 'activo', allowBlank: false},
                {name: 'creado', type: 'date', dateFormat: 'c', allowBlank: true}
            ]
        });
        var writerKiiconnectKaraoke = new Ext.data.JsonWriter({
            encode: true,
            writeAllFields: true
        });
        this.storeKiiconnectKaraoke = new Ext.data.Store({
            id: "id",
            proxy: proxyKiiconnectKaraoke,
            reader: readerKiiconnectKaraoke,
            writer: writerKiiconnectKaraoke,
            autoSave: true
        });
        this.storeKiiconnectKaraoke.load();
        this.gridKiiconnectKaraoke = new Ext.grid.EditorGridPanel({
            height: winHeight - 94,
            store: this.storeKiiconnectKaraoke, columns: [
                new Ext.grid.RowNumberer()
                , {header: 'id', dataIndex: 'id', sortable: true, width: 20}
                , {
                    header: 'Activo',
                    dataIndex: 'activo',
                    sortable: true,
                    width: 30,
                    scope: this,
                    editor: new Ext.form.TextField({allowBlank: false})
                }
                , {
                    header: 'Nombre',
                    dataIndex: 'nombre',
                    sortable: true,
                    width: 40,
                    scope: this,
                    editor: new Ext.form.TextField({allowBlank: false})
                }
                , {
                    header: 'Descripción',
                    dataIndex: 'descripcion',
                    sortable: true,
                    width: 60,
                    scope: this,
                    editor: new Ext.form.TextField({allowBlank: false})
                }
                , {
                    header: 'Slogan',
                    dataIndex: 'slogan',
                    sortable: true,
                    width: 60,
                    scope: this,
                    editor: new Ext.form.TextField({allowBlank: false})
                }
                , {
                    header: 'Icono',
                    dataIndex: 'icono',
                    sortable: true,
                    width: 60,
                    scope: this,
                    editor: new Ext.form.TextField({allowBlank: false})
                }
                , {
                    header: 'URL',
                    dataIndex: 'link',
                    sortable: true,
                    width: 60,
                    scope: this,
                    editor: new Ext.form.TextField({allowBlank: false})
                }

                , {header: 'Creado', dataIndex: 'creado', sortable: true, width: 30, renderer: formatDate}
            ],
            viewConfig: {forceFit: true},
            sm: new Ext.grid.RowSelectionModel({singleSelect: false}),
            border: false,
            stripeRows: true
        });
        //fin Kiiconnect tab

        var desktop = this.app.getDesktop();
        var win = desktop.getWindow('layout-win');

        if (!win) {
            win = desktop.createWindow({
                id: 'grid-win-kiiconnect',
                title: 'Trabajos disponibles',
                width: winWidth,
                height: winHeight,
                iconCls: 'kiiconnect-icon',
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
                            title: 'Kiiconnect Karaoke Galaxy A',
                            iconCls: 'kiiconnect-icon',
                            closable: true,
                            tbar: [

                                {
                                    iconCls: 'demo-grid-add',
                                    handler: this.requestKiiconnectKaraokeData,
                                    scope: this,
                                    text: 'Recargar Datos',
                                    tooltip: 'Recargar datos en la grilla'
                                },
                                '-',
                                {
                                    iconCls: 'demo-grid-add',
                                    handler: this.requestKiiconnectKaraokeDataExport,
                                    scope: this,
                                    text: 'Exportar Datos',
                                    tooltip: 'Exportar datos en la grilla'
                                }
                            ],
                            items: this.gridKiiconnectKaraoke
                        }
                    ]
                })

            });
        }
        win.show();
    },

    /*deletekiiconnect: function () {
     Ext.Msg.show({
     title: 'Confirmación',
     msg: 'Está seguro de querer borrar?',
     scope: this,
     buttons: Ext.Msg.YESNO,
     fn: function (btn) {
     if (btn == 'yes') {
     var rows = this.gridKiiconnect.getSelectionModel().getSelections();
     if (rows.length === 0) {
     return false;
     }
     this.storeKiiconnect.remove(rows);
     }
     }
     });
     }, addkiiconnect: function () {
     var kiiconnect = new this.storeKiiconnect.recordType({
     cargo: '',
     area: '',
     tipo_puesto: '',
     vacantes: '',
     ciudad: '',
     salario: '',
     descripcion: '',
     sexo: '',
     activo: '1',
     creado: ''

     });
     this.gridKiiconnect.stopEditing();
     this.storeKiiconnect.insert(0, kiiconnect);
     this.gridKiiconnect.startEditing(0, 1);
     },  */
    //botones reload, exportar
    requestKiiconnectKaraokeData: function () {
        this.storeKiiconnectKaraoke.load();
    },
    requestKiiconnectKaraokeDataExport: function () {
        Ext.Msg.show({
            title: 'Advertencia',
            msg: 'Descargue el archivo xls  .<br>¿Desea continuar?',
            scope: this,
            icon: Ext.Msg.WARNING,
            buttons: Ext.Msg.YESNO,
            fn: function (btn) {
                if (btn == 'yes') {
                    window.location.href = 'modules/desktop/kiiconnect/server/KiiconnectKaraoke.php';
                }
            }
        });
    }

});

