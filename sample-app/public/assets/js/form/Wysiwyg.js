;

if(window.Smally===undefined) window.Smally = {};

$(document).ready(function(){
	Smally.Wysiwyg();
	if(Smally.ajaxReady){
		Smally.ajaxReady.push('Smally.Wysiwyg');
	}
});

Smally.Wysiwyg = function(){

	$('textarea.jsWysiwyg').tinymce({

        // General options
        theme : "advanced",
        skin : "o2k7",
        skin_variant : "silver",
        plugins : "table,advlink,inlinepopups,preview,media,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras",

        // Theme options
        theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,forecolor,|,justifyleft,justifycenter,justifyright,justifyfull,|,bullist,numlist,|,outdent,indent,|,formatselect,fontsizeselect,|,undo,redo,|,help,print,code,fullscreen",
        theme_advanced_buttons2 : "sub,sup,|,link,unlink,cleanup,|,blockquote,media,|,tablecontrols,|,cut,copy,paste,pastetext,pasteword,|",
        //theme_advanced_buttons3 : "",
        //theme_advanced_buttons4 : "",
        theme_advanced_toolbar_location : "top",
        theme_advanced_toolbar_align : "left",
        theme_advanced_statusbar_location : "bottom",
        theme_advanced_resizing : true,

        // Example content CSS (should be your site CSS)
        content_css : "css/content.css",


    });


};
