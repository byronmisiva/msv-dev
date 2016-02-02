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
var today = new Date().clearTime();
var eventList = {
    "evts": [{
        "id": 1001,
        "cid": 1,
        "title": "Noticias",
        "start": today.add(Date.HOUR, 11).add(Date.MINUTE, 30),
        "end": today.add(Date.HOUR, 12),
        "ad": false
    },
    {
        "id": 1002,
        "cid": 1,
        "title": "Programa 2",
        "start": today.add(Date.HOUR, 12),
        "end": today.add(Date.HOUR, 13),
        "ad": false,
        "rem": "15"
    },
    {
        "id": 1003,
        "cid": 2,
        "title": "Programa canal 2",
        "start": today.add(Date.HOUR, 11).add(Date.MINUTE, 30),
        "end": today.add(Date.HOUR, 12),
        "ad": false
    },
    {
        "id": 1004,
        "cid": 2,
        "title": "Programa  B canal 2",
        "start": today.add(Date.HOUR, 12),
        "end": today.add(Date.HOUR, 13),
        "ad": false
    }]
};
