var elementos = ["#adminmenumain", "#wpfooter", "#bftoobar", "#screen-meta-links", "#tagsdiv-post_tag", "#apv", "#postimagediv", "#minor-publishing", "#wp-content-wrap", " #postexcerpt", "#postdivrich", "#postexcerpt", "#postcustom", "#commentstatusdiv", "#authordiv","#wpdm-attached-dir","#wpdm-settings"];
for (i = 0; i < elementos.length; i++) {
    div = document.querySelector(elementos[i]);
    div.classList.toggle("hidden");
}

jQuery(document).ready(function () {
   // jQuery("#adminmenumain, #wpfooter, #bftoobar, #screen-meta-links, #tagsdiv-post_tag, #apv, #postimagediv, #minor-publishing,#wp-content-wrap, .add-new-h2,  #postexcerpt, #postdivrich, #postexcerpt, #postcustom, #commentstatusdiv, #authordiv,#wpdm-attached-dir,#wpdm-settings").hide();
    jQuery(".add-new-h2").hide();
    jQuery(".add-new-h2").hide();
    jQuery("#wpcontent").css("margin-left", "0");
    jQuery('input:radio[name="file[individual_file_download]"][value="1"]').prop('checked', true);

    jQuery('#publish').click(function(){
        window.parent.functionOcultarIframe();
    });


});



//determina si esta dentro de un iframe
var isInIframe = (window.location != window.parent.location) ? true : false;

window.parent.functionContenido();