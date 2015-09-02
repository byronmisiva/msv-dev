QoDesk.SamsungWindow = Ext.extend(Ext.app.Module, {
    id: 'samsung',
    type: 'desktop/samsung',

    init: function () {
        this.launcher = {
            text: 'Samsung',
            iconCls: 'samsung-icon',
            handler: this.createWindow,
            scope: this
        }
    },

    createWindow: function () {
        var desktop = this.app.getDesktop();
        var win = desktop.getWindow('grid-win-samsung');
        var urlSamsung = "modules/desktop/samsung/server/";
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

         function samsungSexo(id) {
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

         function samsungActivo(id) {
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

         function samsungCargo(id) {
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

         function samsungCampo(id) {
         var index = this.storePrFa.find('id', id);
         if (index > -1) {
         var record = this.storePrFa.getAt(index);
         return record.get('nombre');
         }
         }

         //fin combo campo
         */

        //Samsung tab
        var proxySamsungKaraoke = new Ext.data.HttpProxy({
            api: {
                create: urlSamsung + "crudSamsung.php?operation=insert",
                read: urlSamsung + "crudSamsung.php?operation=selectganadores",
                update: urlSamsung + "crudSamsung.php?operation=update",
                destroy: urlSamsung + "crudSamsung.php?operation=delete"
            }
        });

        var readerSamsungKaraoke = new Ext.data.JsonReader({
            totalProperty: 'total',
            successProperty: 'success',
            messageProperty: 'message',
            idProperty: 'id',
            root: 'data',
            fields: [

                {name: 'id', allowBlank: false},
                {name: 'nombre', allowBlank: false},
                {name: 'telefono', allowBlank: false},
                {name: 'direccion', allowBlank: false},
                {name: 'nombrepremio', allowBlank: false},
                {name: 'creado', type: 'date', dateFormat: 'c', allowBlank: true},
                {name: 'mail', allowBlank: false},
                {name: 'ciudad', allowBlank: false},
                {name: 'consumidor', allowBlank: false},
                {name: 'consumidor-donde', allowBlank: true},
                {name: 'donde', allowBlank: false}
            ]
        });
        var writerSamsungKaraoke = new Ext.data.JsonWriter({
            encode: true,
            writeAllFields: true
        });
        this.storeSamsungKaraoke = new Ext.data.Store({
            id: "id",
            proxy: proxySamsungKaraoke,
            reader: readerSamsungKaraoke,
            writer: writerSamsungKaraoke,
            autoSave: true
        });
        this.storeSamsungKaraoke.load();
        this.gridSamsungKaraoke = new Ext.grid.EditorGridPanel({
            height: winHeight - 94,
            store: this.storeSamsungKaraoke, columns: [
                new Ext.grid.RowNumberer()
                , {header: 'id', dataIndex: 'id', sortable: true, width: 50}
                , {header: 'Nombre', dataIndex: 'nombre', sortable: true, width: 150}
                , {header: 'Teléfono', dataIndex: 'telefono', sortable: true, width: 50}
                , {header: 'Dirección', dataIndex: 'direccion', sortable: true, width: 150}
                , {header: 'Premio', dataIndex: 'nombrepremio', sortable: true, width: 70}
                , {header: 'Creado', dataIndex: 'creado', sortable: true, width: 50, renderer: formatDate}
                , {header: 'Email', dataIndex: 'mail', sortable: true, width: 25, scope: this}
                , {header: 'Ciudad', dataIndex: 'ciudad', sortable: true, width: 30}
                , {header: 'Consumidor', dataIndex: 'consumidor', sortable: true, width: 80}
                , {header: 'Consumidor-donde', dataIndex: 'consumidor-donde', sortable: true, width: 80}
                , {header: 'Donde', dataIndex: 'donde', sortable: true, width: 30, scope: this}

            ],
            viewConfig: {forceFit: true},
            sm: new Ext.grid.RowSelectionModel({singleSelect: false}),
            border: false,
            stripeRows: true
        });
        //fin Samsung tab

        var desktop = this.app.getDesktop();
        var win = desktop.getWindow('layout-win');

        if (!win) {
            win = desktop.createWindow({
                id: 'grid-win-samsung',
                title: 'Trabajos disponibles',
                width: winWidth,
                height: winHeight,
                iconCls: 'samsung-icon',
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
                            title: 'Samsung Karaoke Galaxy A',
                            iconCls: 'samsung-icon',
                            closable: true,
                            tbar: [

                                {
                                    iconCls: 'demo-grid-add',
                                    handler: this.requestSamsungKaraokeData,
                                    scope: this,
                                    text: 'Recargar Datos',
                                    tooltip: 'Recargar datos en la grilla'
                                },
                                '-',
                                {
                                    iconCls: 'demo-grid-add',
                                    handler: this.requestSamsungKaraokeDataExport,
                                    scope: this,
                                    text: 'Exportar Datos',
                                    tooltip: 'Exportar datos en la grilla'
                                }
                            ],
                            items: this.gridSamsungKaraoke
                        }
                    ]
                })

            });
        }
        win.show();
    },

    /*  deletesamsung: function () {
     Ext.Msg.show({
     title: 'Confirmación',
     msg: 'Está seguro de querer borrar?',
     scope: this,
     buttons: Ext.Msg.YESNO,
     fn: function (btn) {
     if (btn == 'yes') {
     var rows = this.gridSamsung.getSelectionModel().getSelections();
     if (rows.length === 0) {
     return false;
     }
     this.storeSamsung.remove(rows);
     }
     }
     });
     }, addsamsung: function () {
     var samsung = new this.storeSamsung.recordType({
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
     this.gridSamsung.stopEditing();
     this.storeSamsung.insert(0, samsung);
     this.gridSamsung.startEditing(0, 1);
     }, deletesamsungCampo: function () {
     Ext.Msg.show({
     title: 'Confirmación',
     msg: 'Está seguro de querer borrar?',
     scope: this,
     buttons: Ext.Msg.YESNO,
     fn: function (btn) {
     if (btn == 'yes') {
     var rows = this.gridSamsungCampoAT.getSelectionModel().getSelections();
     if (rows.length === 0) {
     return false;
     }
     this.storeSamsungCampoAT.remove(rows);
     }
     }
     });
     }, addsamsungCampo: function () {
     var samsungcampo = new this.storeSamsungCampoAT.recordType({
     nombre: ''
     });
     this.gridSamsungCampoAT.stopEditing();
     this.storeSamsungCampoAT.insert(0, samsungcampo);
     this.gridSamsungCampoAT.startEditing(0, 1);
     },

     deletesamsungCargo: function () {
     Ext.Msg.show({
     title: 'Confirmación',
     msg: 'Está seguro de querer borrar?',
     scope: this,
     buttons: Ext.Msg.YESNO,
     fn: function (btn) {
     if (btn == 'yes') {
     var rows = this.gridSamsungCargo.getSelectionModel().getSelections();
     if (rows.length === 0) {
     return false;
     }
     this.storeSamsungCargo.remove(rows);
     }
     }
     });
     }, addsamsungCargo: function () {
     var samsungcargo = new this.storeSamsungCargo.recordType({
     nombre: ''
     });
     this.gridSamsungCargo.stopEditing();
     this.storeSamsungCargo.insert(0, samsungcargo);
     this.gridSamsungCargo.startEditing(0, 1);
     }

     ,
     */

    requestSamsungKaraokeData: function () {
        this.storeSamsungKaraoke.load();
    },
    requestSamsungKaraokeDataExport: function () {
        Ext.Msg.show({
            title: 'Advertencia',
            msg: 'Descargue el archivo xls  .<br>¿Desea continuar?',
            scope: this,
            icon: Ext.Msg.WARNING,
            buttons: Ext.Msg.YESNO,
            fn: function (btn) {
                if (btn == 'yes') {
                    window.location.href = 'modules/desktop/samsung/server/SamsungKaraoke.php';
                }
            }
        });
    }

});

