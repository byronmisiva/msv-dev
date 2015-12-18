QoDesk.RoyalWindow = Ext.extend(Ext.app.Module, {
    id: 'royal',
    type: 'desktop/royal',

    init: function () {
        this.launcher = {
            text: 'Royal',
            iconCls: 'royal-icon',
            handler: this.createWindow,
            scope: this
        }
    },

    createWindow: function () {
        var desktop = this.app.getDesktop();
        var win = desktop.getWindow('grid-win-royal');
        var urlRoyal = "modules/desktop/royal/server/";
        var winWidth = desktop.getWinWidth() / 1.2;
        var winHeight = desktop.getWinHeight() / 1.2;


        //inicio combo activo

        storeSASINO = new Ext.data.JsonStore({
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

        var comboSASINO = new Ext.form.ComboBox({
            id: 'comboSASINO',
            store: storeSASINO,
            valueField: 'id',
            displayField: 'nombre',
            triggerAction: 'all',
            mode: 'local'
        });

        function royalActivo(id) {
            var index = storeSASINO.find('id', id);
            if (index > -1) {
                var record = storeSASINO.getAt(index);
                return record.get('nombre');
            }
        }

        //fin combo activo

        var textField = new Ext.form.TextField({allowBlank: false});

        function formatDate(value) {
            return value ? value.dateFormat('Y-m-d') : '';
        }

        var formatoFechaMax = new Ext.form.DateField({
            format: 'Y-m-d',
            background: '#0000ff'
        });

        function renderGeneraImagen(value, id, r) {
            return '<input type="button" value="Genera Imagen' + value + ' " id="' + value + '"/>';
        }

        function llenaVideo(canvasId, videoSubidoId, nombreArchivoSubido) {
            var canvas = document.getElementById(canvasId);
            var video = document.getElementById(videoSubidoId);
            canvas.width = 200;
            canvas.height = 157;
            canvas.getContext('2d').drawImage(video, 0, 0, 300, 150);

            var Pic = document.getElementById(canvasId).toDataURL("image/png");
            Pic = Pic.replace(/^data:image\/(png|jpg);base64,/, "")

            Ext.Ajax.request({
                url: urlRoyal + 'uploadimagen.php',
                method: 'POST',
                params: {
                    imageData: Pic,
                    nombreArchivoSubido: nombreArchivoSubido
                },
                success: function (response, opts) {
                    var obj = Ext.decode(response.responseText);
                    console.dir(obj);
                },
                failure: function (response, opts) {
                    console.log('server-side failure with status code ' + response.status);
                }
            });
        }

        //Royal tab
        var proxyRoyal = new Ext.data.HttpProxy({
            api: {
                create: urlRoyal + "crudRoyal.php?operation=insert",
                read: urlRoyal + "crudRoyal.php?operation=select",
                update: urlRoyal + "crudRoyal.php?operation=upda",
                destroy: urlRoyal + "crudRoyal.php?operation=delete"
            }
        });

        var readerRoyal = new Ext.data.JsonReader({
            totalProperty: 'total',
            successProperty: 'success',
            messageProperty: 'message',
            idProperty: 'id',
            root: 'data',
            fields: [
                {name: 'id', allowBlank: false},
                {name: 'nombre', allowBlank: false},
                {name: 'apellido', allowBlank: false},
                {name: 'mail', allowBlank: false},
                {name: 'ciudad', allowBlank: false},
                {name: 'fecha_ganador', type: 'date', dateFormat: 'c', allowBlank: true},
                {name: 'telefono', allowBlank: false},
                {name: 'premio', allowBlank: false},
                {name: 'cedula', allowBlank: false},
                {name: 'codigo', allowBlank: false}
            ]
        });

        var writerRoyal = new Ext.data.JsonWriter({
            encode: true,
            writeAllFields: true
        });
        var storeRoyal = new Ext.data.Store({
            id: "id",
            proxy: proxyRoyal,
            reader: readerRoyal,
            writer: writerRoyal,
            autoSave: true
        });
        storeRoyal.load();

        this.storeRoyal = storeRoyal;

        this.gridRoyal = new Ext.grid.EditorGridPanel({
            height: winHeight - 94,
            store: storeRoyal, columns: [
                new Ext.grid.RowNumberer({width: 40})
                , {header: 'Nombre', dataIndex: 'nombre', sortable: true, width: 50, scope: this}
                , {header: 'Apellido', dataIndex: 'apellido', sortable: true, width: 50, scope: this}
                , {header: 'Email', dataIndex: 'mail', sortable: true, width: 50, scope: this}
                , {header: 'Ciudad', dataIndex: 'ciudad', sortable: true, width: 35, scope: this}
                , {header: 'telefono', dataIndex: 'telefono', sortable: true, width: 40, scope: this}
                , {header: 'cedula', dataIndex: 'cedula', sortable: true, width: 40, scope: this}
                , {header: 'premio', dataIndex: 'premio', sortable: true, width: 30, scope: this}
                , {header: 'Fecha Premio', dataIndex: 'fecha_ganador', sortable: true, width: 30, renderer: formatDate}
                , {header: 'Lote Producto', dataIndex: 'codigo', sortable: true, width: 30, scope: this}
            ],
            viewConfig: {forceFit: true},
            sm: new Ext.grid.RowSelectionModel({singleSelect: false}),
            border: false,
            stripeRows: true
        });
        //fin Royal tab

        //RoyalIntentos tab
        var proxyRoyalIntentos = new Ext.data.HttpProxy({
            api: {
                create: urlRoyal + "crudRoyal.php?operation=insert",
                read: urlRoyal + "crudRoyal.php?operation=selectIntentos",
                update: urlRoyal + "crudRoyal.php?operation=upda",
                destroy: urlRoyal + "crudRoyal.php?operation=delete"
            }
        });

        var readerRoyalIntentos = new Ext.data.JsonReader({
            totalProperty: 'total',
            successProperty: 'success',
            messageProperty: 'message',
            idProperty: 'id',
            root: 'data',
            fields: [
                {name: 'id', allowBlank: false},
                {name: 'codigopremio', allowBlank: false},
                {name: 'cedula', allowBlank: false},
                {name: 'completo', allowBlank: false},
                {name: 'ciudad', allowBlank: false},
                {name: 'cedula', allowBlank: false},
                {name: 'creado', type: 'date', dateFormat: 'c', allowBlank: true},
                {name: 'resultado', allowBlank: false},
                {name: 'ip', allowBlank: false}
            ]
        });

        var writerRoyalIntentos = new Ext.data.JsonWriter({
            encode: true,
            writeAllFields: true
        });
        var storeRoyalIntentos = new Ext.data.Store({
            id: "id",
            proxy: proxyRoyalIntentos,
            reader: readerRoyalIntentos,
            writer: writerRoyalIntentos,
            autoSave: true
        });
        storeRoyalIntentos.load();

        this.storeRoyalIntentos = storeRoyalIntentos;

        this.gridRoyalIntentos = new Ext.grid.EditorGridPanel({
            height: winHeight - 94,
            store: storeRoyalIntentos, columns: [
                new Ext.grid.RowNumberer({width: 40})
                , {header: 'completo', dataIndex: 'completo', sortable: true, width: 70, scope: this}
                , {header: 'cedula', dataIndex: 'cedula', sortable: true, width: 30, scope: this}
                , {header: 'ciudad', dataIndex: 'ciudad', sortable: true, width: 20, scope: this}
                , {header: 'Lote producto', dataIndex: 'codigopremio', sortable: true, width: 30, scope: this}
                , {header: 'Fecha intento', dataIndex: 'creado', sortable: true, width: 30, renderer: formatDate}
                , {header: 'ip', dataIndex: 'ip', sortable: true, width: 30, scope: this}
                , {header: 'Seguimiento', dataIndex: 'resultado', sortable: true, width: 40, scope: this}
            ],
            viewConfig: {forceFit: true},
            sm: new Ext.grid.RowSelectionModel({singleSelect: false}),
            border: false,
            stripeRows: true
        });
        //fin RoyalIntentos tab

        //RoyalParticipantes tab
        var proxyRoyalParticipantes = new Ext.data.HttpProxy({
            api: {
                create: urlRoyal + "crudRoyal.php?operation=insert",
                read: urlRoyal + "crudRoyal.php?operation=selectParticipantes",
                update: urlRoyal + "crudRoyal.php?operation=updade",
                destroy: urlRoyal + "crudRoyal.php?operation=delete"
            }
        });

        var readerRoyalParticipantes = new Ext.data.JsonReader({
            totalProperty: 'total',
            successProperty: 'success',
            messageProperty: 'message',
            idProperty: 'id',
            root: 'data',
            fields: [
                {name: 'id', allowBlank: false},
                {name: 'nombre', allowBlank: false},
                {name: 'apellido', allowBlank: false},
                {name: 'mail', allowBlank: false},
                {name: 'telefono', allowBlank: false},
                {name: 'ciudad', allowBlank: false},
                {name: 'cedula', allowBlank: false},
                {name: 'creado', type: 'date', dateFormat: 'c', allowBlank: true}
            ]
        });

        var writerRoyalParticipantes = new Ext.data.JsonWriter({
            encode: true,
            writeAllFields: true
        });
        var storeRoyalParticipantes = new Ext.data.Store({
            id: "id",
            proxy: proxyRoyalParticipantes,
            reader: readerRoyalParticipantes,
            writer: writerRoyalParticipantes,
            autoSave: true
        });
        storeRoyalParticipantes.load();

        this.storeRoyalParticipantes = storeRoyalParticipantes;

        this.gridRoyalParticipantes = new Ext.grid.EditorGridPanel({
            height: winHeight - 94,
            store: storeRoyalParticipantes, columns: [
                new Ext.grid.RowNumberer({width: 40})
                , {header: 'Nombre', dataIndex: 'nombre', sortable: true, width: 35, scope: this}
                , {header: 'apellido', dataIndex: 'apellido', sortable: true, width: 35, scope: this}
                , {header: 'mail', dataIndex: 'mail', sortable: true, width: 70, scope: this}
                , {header: 'cedula', dataIndex: 'cedula', sortable: true, width: 30, scope: this}
                , {header: 'telefono', dataIndex: 'telefono', sortable: true, width: 30, scope: this}
                , {header: 'ciudad', dataIndex: 'ciudad', sortable: true, width: 30, scope: this}
                , {header: 'Fecha registro', dataIndex: 'creado', sortable: true, width: 30, renderer: formatDate}
            ],
            viewConfig: {forceFit: true},
            sm: new Ext.grid.RowSelectionModel({singleSelect: false}),
            border: false,
            stripeRows: true
        });
        //fin RoyalParticipantes tab




        var desktop = this.app.getDesktop();
        var win = desktop.getWindow('layout-win');

        if (!win) {
            win = desktop.createWindow({
                id: 'grid-win-royal',
                title: 'Royal',
                width: winWidth,
                height: winHeight,
                iconCls: 'royal-icon',
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
                            title: 'Ganadores -  Gana Con Royal 2015',
                            iconCls: 'royal-icon',
                            closable: true,
                            tbar: [

                                {
                                    iconCls: 'demo-grid-add',
                                    handler: this.requestRoyalData,
                                    scope: this,
                                    text: 'Recargar Datos',
                                    tooltip: 'Recargar datos en la grilla'
                                },
                                '-',
                                {
                                    iconCls: 'demo-grid-add',
                                    handler: this.requestRoyalDataExport,
                                    scope: this,
                                    text: 'Exportar Datos',
                                    tooltip: 'Exportar datos en la grilla'
                                },
                                '-',
                                {
                                    iconCls: 'demo-grid-add',
                                    handler: this.requestRoyalEstadisticasDataExport,
                                    scope: this,
                                    text: 'Exportar Estadisticas',
                                    tooltip: 'Exportar Estadisticas'
                                }
                            ],
                            items: this.gridRoyal
                        },
                        {
                            autoScroll: true,
                            title: 'Participantes -  Gana Con Royal 2015',
                            iconCls: 'royal-icon',
                            closable: true,
                            tbar: [

                                {
                                    iconCls: 'demo-grid-add',
                                    handler: this.requestRoyalParticipantesData,
                                    scope: this,
                                    text: 'Recargar Datos',
                                    tooltip: 'Recargar datos en la grilla'
                                }, '-',
                                {
                                    iconCls: 'demo-grid-add',
                                    handler: this.requestRoyalParticipantesDataExport,
                                    scope: this,
                                    text: 'Exportar Datos',
                                    tooltip: 'Exportar datos en la grilla'
                                }
                            ],
                            items: this.gridRoyalParticipantes
                        },
                        {
                            autoScroll: true,
                            title: 'Intentos -  Gana Con Royal 2015',
                            iconCls: 'royal-icon',
                            closable: true,
                            tbar: [

                                {
                                    iconCls: 'demo-grid-add',
                                    handler: this.requestRoyalIntentosData,
                                    scope: this,
                                    text: 'Recargar Datos',
                                    tooltip: 'Recargar datos en la grilla'
                                }, '-',
                                {
                                    iconCls: 'demo-grid-add',
                                    handler: this.requestRoyalIntentosDataExport,
                                    scope: this,
                                    text: 'Exportar Datos',
                                    tooltip: 'Exportar datos en la grilla'
                                }
                            ],
                            items: this.gridRoyalIntentos
                        }
                    ]
                })

            });
        }
        win.show();
    },
    requestRoyalParticipantesData: function () {
        this.storeRoyalParticipantes.load();
    },
    requestRoyalParticipantesDataExport: function () {
        Ext.Msg.show({
            title: 'Advertencia',
            msg: 'Descargue el archivo xls  .<br>¿Desea continuar?',
            scope: this,
            icon: Ext.Msg.WARNING,
            buttons: Ext.Msg.YESNO,
            fn: function (btn) {
                if (btn == 'yes') {
                    window.location.href = 'modules/desktop/royal/server/RoyalParticipantes.php';
                }
            }
        });
    },
    requestRoyalIntentosData: function () {
        this.storeRoyalIntentos.load();
    },
    requestRoyalIntentosDataExport: function () {
        Ext.Msg.show({
            title: 'Advertencia',
            msg: 'Descargue el archivo xls  .<br>¿Desea continuar?',
            scope: this,
            icon: Ext.Msg.WARNING,
            buttons: Ext.Msg.YESNO,
            fn: function (btn) {
                if (btn == 'yes') {
                    window.location.href = 'modules/desktop/royal/server/RoyalIntentos.php';
                }
            }
        });
    },
    requestRoyalData: function () {
        this.storeRoyal.load();
    },
    requestRoyalDataExport: function () {
        Ext.Msg.show({
            title: 'Advertencia',
            msg: 'Descargue el archivo xls  .<br>¿Desea continuar?',
            scope: this,
            icon: Ext.Msg.WARNING,
            buttons: Ext.Msg.YESNO,
            fn: function (btn) {
                if (btn == 'yes') {
                    window.location.href = 'modules/desktop/royal/server/Royal.php';
                }
            }
        });
    },
    requestRoyalEstadisticasDataExport: function () {
        Ext.Msg.show({
            title: 'Advertencia',
            msg: 'Descargue el archivo xls  .<br>¿Desea continuar?',
            scope: this,
            icon: Ext.Msg.WARNING,
            buttons: Ext.Msg.YESNO,
            fn: function (btn) {
                if (btn == 'yes') {
                    window.location.href = 'modules/desktop/royal/server/RoyalEstadisticas.php';
                }
            }
        });
    }

});



