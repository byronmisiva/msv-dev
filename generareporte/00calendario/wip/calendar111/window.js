/*!
 * Extensible 1.0.2
 * Copyright(c) 2010-2012 Extensible, LLC
 * licensing@ext.ensible.com
 * http://ext.ensible.com
 */
Ext.onReady(function(){

        if(!this.calendarWin){
            this.calendarWin = new Ext.Window({
                layout: 'fit',
                title: 'En ventana',
                width: 600,
                height: 450,
                modal: true,
                closeAction: 'hide',
                items: {
                    // xtype is supported:
                    xtype: 'extensible.calendarpanel',
                    eventStore: new Ext.ensible.sample.MemoryEventStore({
                        // defined in data/events.js
                        data: Ext.ensible.sample.EventData
                    })
                }
            });
        }
        this.calendarWin.show();


});