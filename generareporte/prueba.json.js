//item xmltv Programa
var proxyXmltvSchedules= new Ext.data.HttpProxy({
    api: {
        create: urlXmltv + "crudXmltvSchedules.php?operation=insert",
        read: urlXmltv + "crudXmltvSchedules.php?operation=select",
        update: urlXmltv + "crudXmltvSchedules.php?operation=update",
        destroy: urlXmltv + "crudXmltvSchedules.php?operation=delete"
    }
});

var readerXmltvSchedules= new Ext.data.JsonReader({
    successProperty: 'success',
    messageProperty: 'message',
    idProperty: 'id',
    root: 'data',
    fields: [
        {name: 'title', allowBlank: false},
        {name: 'date_end', type: 'date', dateFormat: 'c',  allowBlank: true},
        {name: 'date_start', type: 'date', dateFormat: 'c', allowBlank: true},
        {name: 'duration', allowBlank: false},
        {name: 'description', allowBlank: false},
        {name: 'activo', allowBlank: false},
        {name: 'category', allowBlank: false},
        {name: 'imagen', allowBlank: false},
        {name: 'id_channel', allowBlank: false},
        {name: 'id_frecuencia', allowBlank: false}
    ]
});

var writerXmltvSchedules= new Ext.data.JsonWriter({
    encode: true,
    writeAllFields: true
});

this.storeXmltvSchedules= new Ext.data.Store({
    id: "id",
    proxy: proxyXmltvSchedule,
    reader: readerXmltvSchedule,
    writer: writerXmltvSchedule,
    autoSave: true
});
this.storeXmltvSchedule.load();

this.gridXmltvSchedules= new Ext.grid.EditorGridPanel({
    height: winHeight - 144,
    store: this.storeXmltvSchedule, columns: [
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
            width: 120,
            editor: textField
        }, {
            header: 'Tipo',
            dataIndex: 'category',
            sortable: true,
            width: 40,
            editor: comboACCATE, renderer: xmltvCategoria
        },
        {
            header: 'Fecha Inicio',
            dataIndex: 'date_start',
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
            header: 'Duración (m)',
            dataIndex: 'duration',
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
            dataIndex: 'id_channel',
            sortable: true,
            width: 30,
            editor: comboXmlCan, renderer: xmltvCanal
        },
        {
            header: 'Frecuencia',
            dataIndex: 'id_frecuencia',
            sortable: true,
            width: 30,
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


//item xmltv Programa
var proxyXmltvSchedules= new Ext.data.HttpProxy({
    api: {
        create: urlXmltv + "crudXmltvSchedules.php?operation=insert",
        read: urlXmltv + "crudXmltvSchedules.php?operation=select",
        update: urlXmltv + "crudXmltvSchedules.php?operation=update",
        destroy: urlXmltv + "crudXmltvSchedules.php?operation=delete"
    }
});

var readerXmltvSchedules= new Ext.data.JsonReader({
    successProperty: 'success',
    messageProperty: 'message',
    idProperty: 'id',
    root: 'data',
    fields: [
        {name: 'title', allowBlank: false},
        {name: 'date_end', type: 'date', dateFormat: 'c',  allowBlank: true},
        {name: 'date_start', type: 'date', dateFormat: 'c', allowBlank: true},
        {name: 'duration', allowBlank: false},
        {name: 'description', allowBlank: false},
        {name: 'activo', allowBlank: false},
        {name: 'category', allowBlank: false},
        {name: 'imagen', allowBlank: false},
        {name: 'id_channel', allowBlank: false},
        {name: 'id_frecuencia', allowBlank: false}
    ]
});

var writerXmltvSchedules= new Ext.data.JsonWriter({
    encode: true,
    writeAllFields: true
});

this.storeXmltvSchedules= new Ext.data.Store({
    id: "id",
    proxy: proxyXmltvSchedule,
    reader: readerXmltvSchedule,
    writer: writerXmltvSchedule,
    autoSave: true
});
this.storeXmltvSchedule.load();

this.gridXmltvSchedules= new Ext.grid.EditorGridPanel({
    height: winHeight - 144,
    store: this.storeXmltvSchedule, columns: [
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
            width: 120,
            editor: textField
        }, {
            header: 'Tipo',
            dataIndex: 'category',
            sortable: true,
            width: 40,
            editor: comboACCATE, renderer: xmltvCategoria
        },
        {
            header: 'Fecha Inicio',
            dataIndex: 'date_start',
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
            header: 'Duración (m)',
            dataIndex: 'duration',
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
            dataIndex: 'id_channel',
            sortable: true,
            width: 30,
            editor: comboXmlCan, renderer: xmltvCanal
        },
        {
            header: 'Frecuencia',
            dataIndex: 'id_frecuencia',
            sortable: true,
            width: 30,
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
