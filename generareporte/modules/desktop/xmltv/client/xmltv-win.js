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

        function formatHora(value) {
            return value ? value.dateFormat('h:MM:ss') : '';
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

        //inicio combo categoria
        storeACCATE = new Ext.data.JsonStore({
            root: 'users',
            fields: ['id', 'nombre'],
            autoLoad: true,
            data: {
                users: [
                    {"id": "Serie", "nombre": "Serie"},
                    {"id": "Novela", "nombre": "Novela"},
                    {"id": "Noticiero", "nombre": "Noticiero"},
                    {"id": "Pelicula", "nombre": "Pelicula"},
                    {"id": "Variedades", "nombre": "Variedades"},
                    {"id": "Infantil", "nombre": "Infantil"}
                ]
            }
        });

        var comboACCATE = new Ext.form.ComboBox({
            id: 'comboACCATE',
            store: storeACCATE,
            valueField: 'id',
            displayField: 'nombre',
            triggerAction: 'all',
            mode: 'local'
        });

        function xmltvCategoria(id) {
            var index = storeACCATE.find('id', id);
            if (index > -1) {
                var record = storeACCATE.getAt(index);
                return record.get('nombre');
            }
        }

        //fin combo categoria

        //inicio combo Canales
        storeXmlCan = new Ext.data.JsonStore({
            root: 'data',
            fields: ['id', 'display_name'],
            url: urlXmltv + "crudXmltv.php?operation=canales"
        });
        storeXmlCan.load();

        var comboXmlCan = new Ext.form.ComboBox({
            store: storeXmlCan,
            valueField: 'id',
            displayField: 'display_name',
            triggerAction: 'all',
            mode: 'local'
        });

        function xmltvCanal(id) {
            var index = storeXmlCan.find('id', id);
            if (index > -1) {
                var record = storeXmlCan.getAt(index);
                return record.get('display_name');
            }
        }

        //fin combo Programa

        //inicio combo Programas
        storeXmlProg = new Ext.data.JsonStore({
            root: 'data',
            fields: ['id', 'title'],
            url: urlXmltv + "crudXmltv.php?operation=programas"
        });
        storeXmlProg.load();


        var comboXmlProg = new Ext.form.ComboBox({
            store: storeXmlProg,
            valueField: 'id',
            displayField: 'title',
            triggerAction: 'all',
            mode: 'local'
        });
        var idprograma;
        comboXmlProg.on('select', function () {
            //console.log (this.getValue());
            //var record = gridXmltvSchedules.store.getAt(0).data;
            //console.log (record);
            var selectedRecord = gridXmltvSchedules.getSelectionModel().getSelections()[0];

            //validamos que sea nuevo registro, de ser asi llenamos el resto de campos con la información de referencia.
            //if (selectedRecord.data['duration'] == 0) {
                idprograma = this.getValue();
                storeXmlDataProg.load({
                    params: {idprograma: this.getValue()}
                });
            //}
        });

        var storeXmlDataProg = new Ext.data.JsonStore({
            root: 'data',
            fields: ['idchannel', 'id', 'title', 'dateend', 'datestart', 'hora','duration', 'category', 'activo', 'idfrecuencia'],
            url: urlXmltv + "crudXmltv.php?operation=programainfo",
            baseParams: {
                idprograma: ''
            },
            listeners: {
                load: function () {
                    //var selectedRecord = gridXmltvSchedules.getSelectionModel().getSelections()[0];
                    //var row = gridXmltvSchedules.store.indexOf(selectedRecord);
                    var datacargar = storeXmlDataProg.data.items[0].data;
                    var selModel = gridXmltvSchedules.getSelectionModel();
                    // set control of the record to text
                    //NOTE: 'control' here is the value set in the dataIndex property of the Control combobox
                    selModel.getSelections()[0].set('id_channel', datacargar['idchannel']);
                    selModel.getSelections()[0].set('time', datacargar['hora'] );
                    selModel.getSelections()[0].set('duration', datacargar['duration'] );
                    selModel.getSelections()[0].set('id_frecuencia', datacargar['idfrecuencia'] );
                    if (datacargar['dateend'] != null) {
                        selModel.getSelections()[0].set('date_end', new Date(datacargar['dateend']));
                    }

                    if (datacargar['datestart'] != null) {
                        selModel.getSelections()[0].set('date_star', new Date(datacargar['datestart']));
                    }
                }
            }
        });

        function xmltvProgramme(id) {
            var index = storeXmlProg.find('id', id);
            if (index > -1) {
                var record = storeXmlProg.getAt(index);
                return record.get('title');
            }
        }

        //fin combo Programa


        //inicio combo Frecuencia
        storeXmlFre = new Ext.data.JsonStore({
            root: 'data',
            fields: ['id', 'nombre'],
            url: urlXmltv + "crudXmltv.php?operation=frecuencia"
        });
        storeXmlFre.load();

        var comboXmlFre = new Ext.form.ComboBox({
            store: storeXmlFre,
            valueField: 'id',
            displayField: 'nombre',
            triggerAction: 'all',
            mode: 'local'
        });

        function xmltvFrecuencia(id) {
            var index = storeXmlFre.find('id', id);
            if (index > -1) {
                var record = storeXmlFre.getAt(index);
                return record.get('nombre');
            }
        }

        //fin combo frecuencia

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

        //item xmltv canal
        var proxyXmltv = new Ext.data.HttpProxy({
            api: {
                create: urlXmltv + "crudXmltvChannel.php?operation=insert",
                read: urlXmltv + "crudXmltvChannel.php?operation=select",
                update: urlXmltv + "crudXmltvChannel.php?operation=update",
                destroy: urlXmltv + "crudXmltvChannel.php?operation=delete"
            }
        });
        var readerXmltv = new Ext.data.JsonReader({

            successProperty: 'success',
            messageProperty: 'message',
            idProperty: 'id',
            root: 'data',
            fields: [
                {name: 'id', allowBlank: true},
                {name: 'id_code', allowBlank: true},
                {name: 'display_name', allowBlank: false},
                {name: 'description', allowBlank: false},
                {name: 'tag', allowBlank: false},
                {name: 'icon', allowBlank: false},
                {name: 'activo', allowBlank: false},
                {name: 'order', allowBlank: false}
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
                    header: 'Id',
                    dataIndex: 'id',
                    width: 12
                }
                ,
                {
                    header: 'Name',
                    dataIndex: 'display_name',
                    sortable: true,
                    width: 80,
                    editor: new Ext.form.TextField({allowBlank: false})
                }
                , {
                    header: 'Code channel*',
                    dataIndex: 'id_code',
                    sortable: true,
                    width: 30,
                    editor: new Ext.form.TextField({allowBlank: false})
                }

                , {
                    header: 'Tag',
                    dataIndex: 'tag',
                    sortable: true,
                    width: 80,
                    editor: new Ext.form.TextField({allowBlank: false})
                }
                ,
                {
                    header: 'Description',
                    dataIndex: 'description',
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
                    header: 'Order',
                    dataIndex: 'order',
                    sortable: true,
                    width: 30,
                    editor: numberField
                }
                , {
                    header: 'Icono',
                    dataIndex: 'icon',
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
        // fin xmltv canal

        //item xmltv Programa
        var proxyXmltvPrograma = new Ext.data.HttpProxy({
            api: {
                create: urlXmltv + "crudXmltvProgramme.php?operation=insert",
                read: urlXmltv + "crudXmltvProgramme.php?operation=select",
                update: urlXmltv + "crudXmltvProgramme.php?operation=update",
                destroy: urlXmltv + "crudXmltvProgramme.php?operation=delete"
            }
        });

        var readerXmltvPrograma = new Ext.data.JsonReader({
            successProperty: 'success',
            messageProperty: 'message',
            idProperty: 'id',
            root: 'data',
            fields: [
                {name: 'title', allowBlank: false},
                {name: 'date_end', type: 'date', dateFormat: 'c', allowBlank: true},
                {name: 'date_start', type: 'date', dateFormat: 'c', allowBlank: true},
                {name: 'hora', allowBlank: false},
                {name: 'duration', allowBlank: false},
                {name: 'description', allowBlank: false},
                {name: 'activo', allowBlank: false},
                {name: 'category', allowBlank: false},
                {name: 'imagen', allowBlank: false},
                {name: 'id_channel', allowBlank: false},
                {name: 'id_frecuencia', allowBlank: false}
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
                    dataIndex: 'title',
                    sortable: true,
                    width: 40,
                    editor: textField
                }, {
                    header: 'Descripción',
                    dataIndex: 'description',
                    sortable: true,
                    width: 100,
                    editor: textField
                }, {
                    header: 'Tipo',
                    dataIndex: 'category',
                    sortable: true,
                    width: 30,
                    editor: comboACCATE, renderer: xmltvCategoria
                },
                {
                    header: 'Fecha Inicio',
                    dataIndex: 'date_start',
                    sortable: true,
                    width: 30,
                    editor: formatoFecha,
                    renderer: formatDate
                },
                {
                    header: 'Fecha Fin*',
                    dataIndex: 'date_end',
                    sortable: true,
                    width: 30,
                    editor: formatoFecha,
                    renderer: formatDate
                },
                {
                    header: 'Hora',
                    dataIndex: 'hora',
                    sortable: true,
                    width: 25,
                    editor: numberField
                },
                {
                    header: 'Duración(m)',
                    dataIndex: 'duration',
                    sortable: true,
                    width: 25,
                    editor: numberField
                },

                {
                    header: 'Activo',
                    dataIndex: 'activo',
                    sortable: true,
                    width: 20,
                    editor: comboACXC, renderer: xmltvActivo
                },
                {
                    header: 'Canal',
                    dataIndex: 'id_channel',
                    sortable: true,
                    width: 50,
                    editor: comboXmlCan, renderer: xmltvCanal
                },
                {
                    header: 'Frecuencia',
                    dataIndex: 'id_frecuencia',
                    sortable: true,
                    width: 50,
                    editor: comboXmlFre, renderer: xmltvFrecuencia
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
        // fin xmltv Programa


        //item xmltv Schedules
        var proxyXmltvSchedules = new Ext.data.HttpProxy({
            api: {
                create: urlXmltv + "crudXmltvSchedules.php?operation=insert",
                read: urlXmltv + "crudXmltvSchedules.php?operation=select",
                update: urlXmltv + "crudXmltvSchedules.php?operation=update",
                destroy: urlXmltv + "crudXmltvSchedules.php?operation=delete"
            }
        });

        var readerXmltvSchedules = new Ext.data.JsonReader({
            successProperty: 'success',
            messageProperty: 'message',
            idProperty: 'id',
            root: 'data',
            fields: [
                {name: 'id_programme', allowBlank: false},
                {name: 'id_channel', allowBlank: false},
                {name: 'description', allowBlank: true},
                {name: 'date_star', type: 'date', dateFormat: 'c', allowBlank: true},
                {name: 'date_end', type: 'date', dateFormat: 'c', allowBlank: true},
                {name: 'time', allowBlank: false},
                {name: 'duration', allowBlank: false},
                {name: 'id_frecuencia', allowBlank: false},
                {name: 'activo', allowBlank: false}
            ]
        });

        var writerXmltvSchedules = new Ext.data.JsonWriter({
            encode: true,
            writeAllFields: true
        });

        this.storeXmltvSchedules = new Ext.data.Store({
            id: "id",
            proxy: proxyXmltvSchedules,
            reader: readerXmltvSchedules,
            writer: writerXmltvSchedules,
            autoSave: true
        });
        this.storeXmltvSchedules.load();

        this.gridXmltvSchedules = new Ext.grid.EditorGridPanel({
            height: winHeight - 144,
            store: this.storeXmltvSchedules, columns: [
                new Ext.grid.RowNumberer(),
                {
                    header: 'id_programme',
                    dataIndex: 'id_programme',
                    sortable: true,
                    width: 40,
                    editor: comboXmlProg, renderer: xmltvProgramme
                }, {
                    header: 'id_channel',
                    dataIndex: 'id_channel',
                    sortable: true,
                    width: 40,
                    editor: comboXmlCan, renderer: xmltvCanal
                }, {
                    header: 'Descripción',
                    dataIndex: 'description',
                    sortable: true,
                    width: 120,
                    editor: textField
                }, {
                    header: 'Fecha Inicio',
                    dataIndex: 'date_star',
                    sortable: true,
                    width: 50,
                    editor: formatoFecha,
                    renderer: formatDate
                },
                {
                    header: 'Fecha Fin*',
                    dataIndex: 'date_end',
                    sortable: true,
                    width: 50,
                    editor: formatoFecha,
                    renderer: formatDate
                },
                {
                    header: 'time',
                    dataIndex: 'time',
                    sortable: true,
                    width: 30,
                    editor: numberField
                },
                {
                    header: 'Duración (m)',
                    dataIndex: 'duration',
                    sortable: true,
                    width: 30,
                    editor: numberField
                },
                {
                    header: 'Frecuencia',
                    dataIndex: 'id_frecuencia',
                    sortable: true,
                    width: 50,
                    editor: comboXmlFre, renderer: xmltvFrecuencia
                },

                {
                    header: 'Activo',
                    dataIndex: 'activo',
                    sortable: true,
                    width: 30,
                    editor: comboACXC, renderer: xmltvActivo
                }
            ],
            viewConfig: {forceFit: true},
            sm: new Ext.grid.RowSelectionModel({singleSelect: false}),
            border: false,
            stripeRows: true
        });

        var gridXmltvSchedules = this.gridXmltvSchedules;
        // fin xmltv Schedules

        //item xmltv Eventos

        //store combo listado canales
        /*this.calendarStore = new Ext.data.JsonStore({
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
         });*/

        this.calendarStore = new Ext.data.JsonStore({
            storeId: 'calendarStore',
            root: 'calendars',
            idProperty: 'id',
            data: calendarList, // defined in calendar-list.js
            proxy: new Ext.data.MemoryProxy(),
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
        // store eventos
        this.eventStore = new Ext.data.JsonStore({
            id: 'eventStore',
            root: 'evts',
            data: eventList, // defined in event-list.js
            proxy: new Ext.data.MemoryProxy(),
            fields: Ext.calendar.EventRecord.prototype.fields.getRange(),
            sortInfo: {
                field: 'StartDate',
                direction: 'ASC'
            }
        });
        // fin store eventos

        var proxyXmltvEventos = new Ext.data.HttpProxy({
            api: {
                create: urlXmltv + "crudXmltvEventos.php?operation=insert",
                read: urlXmltv + "crudXmltvEventos.php?operation=select",
                update: urlXmltv + "crudXmltvEventos.php?operation=update",
                destroy: urlXmltv + "crudXmltvEventos.php?operation=delete"
            }
        });

        var readerXmltvEventos = new Ext.data.JsonReader({
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

        var writerXmltvEventos = new Ext.data.JsonWriter({
            encode: true,
            writeAllFields: true
        });

        this.storeXmltvEventos = new Ext.data.Store({
            id: "id",
            proxy: proxyXmltvEventos,
            reader: readerXmltvEventos,
            writer: writerXmltvEventos,
            autoSave: true
        });
        this.storeXmltvEventos.load();

        this.calendarXmltvEventos = new Ext.grid.EditorGridPanel({
            height: winHeight - 144,
            store: this.storeXmltvEventos, columns: [
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
        // fin xmltvEventos

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
                        },

                        {
                            autoScroll: true,
                            title: 'Schedules',
                            closable: true,
                            tbar: [
                                {text: 'Nuevo', scope: this, handler: this.addxmltvSchedules, iconCls: 'add-icon'},
                                '-',
                                {
                                    text: "Eliminar",
                                    scope: this,
                                    handler: this.deletexmltvSchedules,
                                    iconCls: 'delete-icon'
                                },
                                '-', {
                                    iconCls: 'demo-grid-add',
                                    handler: this.requestXmltvSchedulesData,
                                    scope: this,
                                    text: 'Recargar Datos', iconCls: 'x-tbar-loading'
                                }
                            ],
                            items: gridXmltvSchedules
                        }/*,


                         {
                         autoScroll: true,
                         title: 'Programación Xmltv',
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
                         handler: this.requestXmltvEventosData,
                         scope: this,
                         text: 'Recargar Datos', iconCls: 'x-tbar-loading'
                         }
                         ],
                         items:  {
                         xtype: 'calendarpanel',
                         eventStore: this.eventStore,
                         calendarStore: this.calendarStore,
                         border: false,
                         id:'app-calendar',
                         region: 'center',
                         activeItem: 2, // month view

                         // CalendarPanel supports view-specific configs that are passed through to the
                         // underlying views to make configuration possible without explicitly having to
                         // create those views at this level:
                         monthViewCfg: {
                         showHeader: true,
                         showWeekLinks: true,
                         showWeekNumbers: true
                         },

                         // Some optional CalendarPanel configs to experiment with:
                         //showDayView: false,
                         //showWeekView: false,
                         //showMonthView: false,
                         //showNavBar: false,
                         //showTodayText: false,
                         //showTime: false,
                         //title: 'My Calendar', // the header of the calendar, could be a subtitle for the app

                         // Once this component inits it will set a reference to itself as an application
                         // member property for easy reference in other functions within App.


                         listeners: {
                         'eventclick': {
                         fn: function(vw, rec, el){
                         this.showEditWindow(rec, el);
                         //this.clearMsg();
                         },
                         scope: this
                         },
                         'eventover': function(vw, rec, el){
                         //console.log('Entered evt rec='+rec.data.Title+', view='+ vw.id +', el='+el.id);
                         },
                         'eventout': function(vw, rec, el){
                         //console.log('Leaving evt rec='+rec.data.Title+', view='+ vw.id +', el='+el.id);
                         },
                         'eventadd': {
                         fn: function(cp, rec){
                         this.showMsg('Event '+ rec.data.Title +' was added');
                         },
                         scope: this
                         },
                         'eventupdate': {
                         fn: function(cp, rec){
                         this.showMsg('Event '+ rec.data.Title +' was updated');
                         },
                         scope: this
                         },
                         'eventdelete': {
                         fn: function(cp, rec){
                         this.showMsg('Event '+ rec.data.Title +' was deleted');
                         },
                         scope: this
                         },
                         'eventcancel': {
                         fn: function(cp, rec){
                         // edit canceled
                         },
                         scope: this
                         },
                         'viewchange': {
                         fn: function(p, vw, dateInfo){
                         if(this.editWin){
                         this.editWin.hide();
                         };
                         if(dateInfo !== null){
                         // will be null when switching to the event edit form so ignore

                         //this.updateTitle(dateInfo.viewStart, dateInfo.viewEnd);
                         }
                         },
                         scope: this
                         },
                         'dayclick': {
                         fn: function(vw, dt, ad, el){
                         this.showEditWindow({
                         StartDate: dt,
                         IsAllDay: ad
                         }, el);
                         //this.clearMsg();
                         },
                         scope: this
                         },
                         'rangeselect': {
                         fn: function(win, dates, onComplete){
                         this.showEditWindow(dates);
                         this.editWin.on('hide', onComplete, this, {single:true});
                         //this.clearMsg();
                         },
                         scope: this
                         },
                         'eventmove': {
                         fn: function(vw, rec){
                         rec.commit();
                         var time = rec.data.IsAllDay ? '' : ' \\a\\t g:i a';
                         //this.showMsg('Event '+ rec.data.Title +' was moved to '+rec.data.StartDate.format('F jS'+time));
                         },
                         scope: this
                         },
                         'eventresize': {
                         fn: function(vw, rec){
                         rec.commit();
                         //this.showMsg('Event '+ rec.data.Title +' was updated');
                         },
                         scope: this
                         },
                         'eventdelete': {
                         fn: function(win, rec){
                         this.eventStore.remove(rec);
                         ///this.showMsg('Event '+ rec.data.Title +' was deleted');
                         },
                         scope: this
                         },
                         'initdrag': {
                         fn: function(vw){
                         if(this.editWin && this.editWin.isVisible()){
                         this.editWin.hide();
                         }
                         },
                         scope: this
                         }
                         }
                         }
                         }
                         */
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
            id_code: '',
            display_name: '',
            tag: '',
            description: '',
            icon: '',
            activo: '0',
            order: '0'
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

    deletexmltvSchedules: function () {
        Ext.Msg.show({
            title: 'Confirmación',
            msg: 'Está seguro de querer borrar?',
            scope: this,
            buttons: Ext.Msg.YESNO,
            fn: function (btn) {
                if (btn == 'yes') {
                    var rows = this.gridXmltvSchedules.getSelectionModel().getSelections();
                    if (rows.length === 0) {
                        return false;
                    }
                    this.storeXmltvSchedules.remove(rows);
                }
            }
        });
    },
    addxmltvSchedules: function () {
        var xmltvSchedules = new this.storeXmltvSchedules.recordType({
            title: '',
            date_end: '',
            date_start: '',
            duration: 0,
            description: '',
            activo: '',
            imagen: '',
            category: '',
            id_channel: '',
            id_frecuencia: ''

        });
        this.gridXmltvSchedules.stopEditing();
        this.storeXmltvSchedules.insert(0, xmltvSchedules);
        this.gridXmltvSchedules.startEditing(0, 1);

        this.gridXmltvSchedules.getSelectionModel().selectRow(0);
        this.gridXmltvSchedules.fireEvent('rowclick', this.gridXmltvSchedules, 0)
    },

    requestXmltvSchedulesData: function () {
        this.storeXmltvSchedules.load();
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
            title: '',
            date_end: '',
            date_start: '',
            duration: 0,
            description: '',
            activo: 0,
            imagen: '',
            category: '',
            id_channel: '',
            id_frecuencia: ''

        });
        this.gridXmltvPrograma.stopEditing();
        this.storeXmltvPrograma.insert(0, xmltvPrograma);
        this.gridXmltvPrograma.startEditing(0, 1);
    },
    requestXmltvProgramaData: function () {
        this.storeXmltvPrograma.load();
        storeXmlCan.load();
        this.storeXmltvFILE.load();
    },


    enviarMensajeXmltv: function () {
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
    requestXmltvEventosData: function () {
        this.storeXmltvEventos.load();
    }

});
