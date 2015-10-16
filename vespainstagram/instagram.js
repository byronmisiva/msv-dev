/**
 * Created by byronherrera on 1/10/15.
 */

image_link = "https:\/\/scontent.cdninstagram.com\/hphotos-xaf1\/t51.2885-15\/s480x480\/e35\/12070678_402976259906562_1117662881_n.jpg";
caption = "\ud83c\udfb6Can't touch this.\ud83c\udfb6 #DGR #VespaEcuador #vitavespa";
user = "pendejotron";
userPicture = "https:\/\/igcdn-photos-d-a.akamaihd.net\/hphotos-ak-xaf1\/t51.2885-19\/11849142_497626173734475_2017605201_a.jpg";
id = "1083757222669869300_16580023";
likes ="16 \u2665";

post =  '<div class="ca"><div class="header_ca"><img  src="' +userPicture+ '" class="userPicture"><div class="user_ca">' +user+ '</div></div>' + 
        '<div class="contenimage"><a href=\'#\'onclick="window.open("instagram://media?id='+ id + '", "_system", "location=yes"); return false;"> <img  src="' +image_link+ '"></a></div>' +
        '<div class="caption_ca">'+ likes +' ♥ </br>' +caption+ '</div></div>';

//document.getElementById('instagram').appendChild(post);

console.log (post)