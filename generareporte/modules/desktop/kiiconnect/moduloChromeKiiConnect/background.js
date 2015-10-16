var PROJECT_ID = "397410313070"; // senderId or ProjectId

// Returns a new notification ID used in the notification.
function getNotificationId() {
    var id = Math.floor(Math.random() * 9007199254740992) + 1;
    return id.toString();
}
function clickBotonPushWoosh() {
    var btn = document.querySelectorAll("#Wooosh")
    if (typeof btn != 'undefined') {
        if (typeof btn[0] != 'undefined') {
            btn[0].addEventListener("click", function () {

                var filtro = document.querySelectorAll("#filters input:checked")

                if (typeof filtro != 'undefined') {
                    filtro = filtro[0].value;
                } else {
                    richpage = 0
                }

                var richpage = document.querySelectorAll(".input-group input");

                if (typeof richpage != 'undefined') {
                    richpage = richpage[5].value;
                    if (richpage != "")
                        richpage = richpage.match(/\[(.*)\]/)[1];
                    else
                        richpage = 0;
                } else {
                    richpage = 0
                }
                // en el campo de titulo para chrome envio el valor
                document.getElementById("chrome-title").value = richpage + ";" + filtro;
                event.stopImmediatePropagation();
            })
        }
    }
}

function messageReceived(message) {
    // A message is an object with a data property that
    // consists of key-value pairs.
    console.log("Message received: " + JSON.stringify(message.data));
    // Pop up a notification to show the GCM message.
    // If you want use images from remote resource add it to manifest permissions
    // https://developer.chrome.com/apps/app_codelab8_webresources
    chrome.notifications.create(getNotificationId(), {
        title: message.data.header || message.data.body,
        iconUrl: message.data.i || 'logo.png',
        type: 'basic',
        message: message.data.body
    }, function (notificationId) {
        if (chrome.runtime.lastError) {
            // When the registration fails, handle the error and retry the registration later.
            // See error codes here https://developer.chrome.com/extensions/cloudMessaging#error_reference
            console.log("Fail to create the message: " + chrome.runtime.lastError.message);
            return;
        }
    });


    $.post("http://www.misiva.com.ec/generareporte/modules/desktop/kiiconnect/server/dataKiiconnect.php", {
        "body": message.data.body,
        "header": message.data.header,
        "l": message.data.l,
        "p": message.data.p
        /*,
         "longuitud": longuitud,
         "latitud": latitud,
         "richpage": richpage*/
    }, function (data) {
        console.log(data);
    });

    chrome.storage.local.set({
        messageHash: message.data.p,
        richPageOld: message.data.h,
        url: message.data.l
    });
}

var appWindow = null;

function createWindow() {
    /* chrome.app.window.create(
     "register.html",
     {
     width: 500,
     height: 400,
     frame: 'chrome',
     resizable: true
     },
     function(appWin) {
     appWindow = appWin;
     appWin.onClosed.addListener(function() {
     console.log('Window is closed');
     appWindow = null;
     });
     }
     );
     */
}

function firstTimeRegistration() {
    //  createWindow();
}

function pushClickEvent() {
    pushwooshStatistics();
    chrome.storage.local.get(['url', 'richPageOld'], function (items) {
        if (items.url) {
            window.open(items.url, '_newtab');
        }
        else if (items.richPageOld) {
            window.open('https://cp.pushwoosh.com/pages/' + items.richPageOld, '_newtab');
        }
        chrome.storage.local.remove(['url', 'richPageOld']);
    });
    if (appWindow != null) {
        console.log('Window is restored');
        appWindow.show();
    } else {
        console.log('Window is created');
        createWindow();
    }
}

var intervalo = setTimeout(function () {
    clickBotonPushWoosh();
}, 5000);

// Set up a listener for GCM message event.
chrome.gcm.onMessage.addListener(messageReceived);

// Set up listeners to trigger the first time registration.
chrome.runtime.onInstalled.addListener(firstTimeRegistration);
chrome.runtime.onStartup.addListener(firstTimeRegistration);
// Add listener for send push-open statistics to Pushwoosh
chrome.notifications.onClicked.addListener(pushClickEvent);

function callbackerror() {

}