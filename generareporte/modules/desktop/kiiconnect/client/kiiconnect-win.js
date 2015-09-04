QoDesk.KiiconnectWindow = Ext.extend(Ext.app.Module, {
    id:'kiiconnect',
    type:'desktop/kiiconnect',

    init:function () {
        this.launcher = {
            text:'kiiconnect',
            iconCls:'kiiconnect-icon',
            handler:this.createWindow,
            scope:this
        }
    },

    createWindow:function () {
        var desktop = this.app.getDesktop();
        var win = desktop.getWindow('grid-win-kiiconnect');

        var urlKiiconnect = "modules/desktop/kiiconnect/server/";
        var winWidth = desktop.getWinWidth() / 1.2;
        var winHeight = desktop.getWinHeight() / 1.2;

        //inicio combo activo

        storeOFAC = new Ext.data.JsonStore({
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
            store:  storeOFAC,
            valueField: 'id',
            displayField: 'nombre',
            triggerAction: 'all',
            mode: 'local'
        });

        function kiiconnectActivo(id) {
            var index =  storeOFAC.find('id', id);
            if (index > -1) {
                var record =  storeOFAC.getAt(index);
                return record.get('nombre');
            }
        }

        //fin combo activo

        //inicio combo Producto
        storePrFa = new Ext.data.JsonStore({
            root:'data',
            fields:['id', 'nombre'],
            url: urlKiiconnect +  "crudKiiconnect.php?operation=categorias"
        });
        storePrFa.load();

        var comboPrFa = new Ext.form.ComboBox({
            store:storePrFa,
            valueField:'id',
            displayField:'nombre',
            triggerAction:'all',
            mode:'local'
        });

        function kiiconnectCategoria(id) {
            var index =  storePrFa.find('id', id);
            if (index > -1) {
                var record = storePrFa.getAt(index);
                return record.get('nombre');
            }
        }

        //fin combo Producto

        //item kiiconnect

        var proxyKiiconnect = new Ext.data.HttpProxy({
            api:{
                create: urlKiiconnect +  "crudKiiconnect.php?operation=insert",
                read:urlKiiconnect + "crudKiiconnect.php?operation=select",
                update:urlKiiconnect + "crudKiiconnect.php?operation=update",
                destroy:urlKiiconnect +  "crudKiiconnect.php?operation=delete"
            }
        });

        var readerKiiconnect = new Ext.data.JsonReader({

            successProperty:'success',
            messageProperty:'message',
            idProperty:'id',
            root:'data',
            fields:[
                {name: 'nombre', allowBlank: false},
                {name: 'descripcion', allowBlank: false},
                {name: 'icono', allowBlank: false},
                {name: 'link', allowBlank: false},
                {name: 'activo', allowBlank: false},
                {name: 'orden', allowBlank: false},
                {name: 'id_categoria', allowBlank: false}
            ]
        });

        var writerKiiconnect = new Ext.data.JsonWriter({
            encode:true,
            writeAllFields:true
        });

        this.storeKiiconnect = new Ext.data.Store({
            id:"id",
            proxy:proxyKiiconnect,
            reader:readerKiiconnect,
            writer:writerKiiconnect,
            autoSave:true
        });
        this.storeKiiconnect.load();

        var textField = new Ext.form.TextField({allowBlank:false});

        this.gridKiiconnect = new Ext.grid.EditorGridPanel({
            store:this.storeKiiconnect, columns:[
                new Ext.grid.RowNumberer(),
                {
                    header:'nombre',
                    dataIndex:'nombre',
                    sortable:true,
                    width:80,
                    editor:new Ext.form.TextField({allowBlank:false})
                }
                ,
                {
                    header:'Descripción',
                    dataIndex:'descripcion',
                    sortable:true,
                    width:80,
                    editor:new Ext.form.TextField({allowBlank:false})
                }
                ,
                {
                    header:'Icono',
                    dataIndex:'icono',
                    sortable:true,
                    width:80,
                    editor:new Ext.form.TextField({allowBlank:false})
                }
                ,
                {
                    header:'Link',
                    dataIndex:'link',
                    sortable:true,
                    width:80,
                    editor:new Ext.form.TextField({allowBlank:false})
                },
                {
                    header:'Activo',
                    dataIndex:'activo',
                    sortable:true,
                    width:80,
                    editor:comboOFAC, renderer:kiiconnectActivo
                },
                {
                    header:'Orden',
                    dataIndex:'orden',
                    sortable:true,
                    width:80,
                    editor:new Ext.form.TextField({allowBlank:false})
                },
                {
                    header:'id_categoria',
                    dataIndex:'id_categoria',
                    sortable:true,
                    width:80,
                    editor:comboPrFa, renderer:kiiconnectCategoria
                }

            ],
            viewConfig:{forceFit:true},
            sm:new Ext.grid.RowSelectionModel({singleSelect:false}),
            border:false,
            stripeRows:true
        });

        // fin kiiconnect

        if (!win) {
            win = desktop.createWindow({
                id:'grid-win-kiiconnect',
                title:'Kiiconnect',
                width:winWidth,
                height:winHeight,
                iconCls:'kiiconnect-icon',
                shim:false,
                animCollapse:false,
                constrainHeader:true,
                layout:'fit',
                tbar:[
                    {text:'Nuevo', scope:this, handler:this.addkiiconnect, iconCls:'save-icon'},
                    '-',
                    {text:"Eliminar", scope:this, handler:this.deletekiiconnect, iconCls:'delete-icon'}
                ],
                items:this.gridKiiconnect
            });
        }
        win.show();
    }, deletekiiconnect:function () {
        Ext.Msg.show({
            title:'Confirmación',
            msg:'Está seguro de querer borrar?',
            scope:this,
            buttons:Ext.Msg.YESNO,
            fn:function (btn) {
                if (btn == 'yes') {
                    var rows = this.gridKiiconnect.getSelectionModel().getSelections();
                    if (rows.length === 0) {
                        return false;
                    }
                    this.storeKiiconnect.remove(rows);
                }
            }
        });
    }, addkiiconnect:function () {
        var kiiconnect = new this.storeKiiconnect.recordType({
            nombre:'',
            descripcion:'',
            icono:'',
            link:'',
            activo:'',
            orden:'',
            id_categoria:''
        });
        this.gridKiiconnect.stopEditing();
        this.storeKiiconnect.insert(0, kiiconnect);
        this.gridKiiconnect.startEditing(0, 1);
    }

});
