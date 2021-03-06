/*
This file is part of Ext JS 3.4

Copyright (c) 2011-2013 Sencha Inc

Contact:  http://www.sencha.com/contact

Commercial Usage
Licensees holding valid commercial licenses may use this file in accordance with the Commercial
Software License Agreement provided with the Software or, alternatively, in accordance with the
terms contained in a written agreement between you and Sencha.

If you are unsure which license is appropriate for your use, please contact the sales department
at http://www.sencha.com/contact.

Build date: 2013-04-03 15:07:25
*/
/**
 * @class Ext.data.ArrayReader
 * @extends Ext.data.JsonReader
 * <p>Data reader class to create an Array of {@link Ext.data.Record} objects from an Array.
 * Each element of that Array represents a row of data fields. The
 * fields are pulled into a Record object using as a subscript, the <code>mapping</code> property
 * of the field definition if it exists, or the field's ordinal position in the definition.</p>
 * <p>Example code:</p>
 * <pre><code>
var Employee = Ext.data.Record.create([
    {name: 'name', mapping: 1},         // "mapping" only needed if an "id" field is present which
    {name: 'occupation', mapping: 2}    // precludes using the ordinal position as the index.
]);
var myReader = new Ext.data.ArrayReader({
    {@link #idIndex}: 0
}, Employee);
</code></pre>
 * <p>This would consume an Array like this:</p>
 * <pre><code>
[ [1, 'Bill', 'Gardener'], [2, 'Ben', 'Horticulturalist'] ]
 * </code></pre>
 * @constructor
 * Create a new ArrayReader
 * @param {Object} meta Metadata configuration options.
 * @param {Array/Object} recordType
 * <p>Either an Array of {@link Ext.data.Field Field} definition objects (which
 * will be passed to {@link Ext.data.Record#create}, or a {@link Ext.data.Record Record}
 * constructor created from {@link Ext.data.Record#create}.</p>
 */
Ext.data.ArrayReader = Ext.extend(Ext.data.JsonReader, {
    /**
     * @cfg {String} successProperty
     * @hide
     */
    /**
     * @cfg {Number} id (optional) The subscript within row Array that provides an ID for the Record.
     * Deprecated. Use {@link #idIndex} instead.
     */
    /**
     * @cfg {Number} idIndex (optional) The subscript within row Array that provides an ID for the Record.
     */
    /**
     * Create a data block containing Ext.data.Records from an Array.
     * @param {Object} o An Array of row objects which represents the dataset.
     * @return {Object} data A data block which is used by an Ext.data.Store object as
     * a cache of Ext.data.Records.
     */
    readRecords : function(o){
        this.arrayData = o;
        var s = this.meta,
            sid = s ? Ext.num(s.idIndex, s.id) : null,
            recordType = this.recordType,
            fields = recordType.prototype.fields,
            records = [],
            success = true,
            v;

        var root = this.getRoot(o);

        for(var i = 0, len = root.length; i < len; i++) {
            var n = root[i],
                values = {},
                id = ((sid || sid === 0) && n[sid] !== undefined && n[sid] !== "" ? n[sid] : null);
            for(var j = 0, jlen = fields.length; j < jlen; j++) {
                var f = fields.items[j],
                    k = f.mapping !== undefined && f.mapping !== null ? f.mapping : j;
                v = n[k] !== undefined ? n[k] : f.defaultValue;
                v = f.convert(v, n);
                values[f.name] = v;
            }
            var record = new recordType(values, id);
            record.json = n;
            records[records.length] = record;
        }

        var totalRecords = records.length;

        if(s.totalProperty) {
            v = parseInt(this.getTotal(o), 10);
            if(!isNaN(v)) {
                totalRecords = v;
            }
        }
        if(s.successProperty){
            v = this.getSuccess(o);
            if(v === false || v === 'false'){
                success = false;
            }
        }

        return {
            success : success,
            records : records,
            totalRecords : totalRecords
        };
    }
});