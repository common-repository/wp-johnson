<?php
    $url_with_file = $_SERVER['HTTP_REFERER'];
    $file_pos = strpos( $url_with_file, "/wp-admin" );
    $url = substr( $url_with_file, 0, $file_pos );
    
    require_once('../../../../../wp-load.php');
    
    global $wpj;
   
    $fonts = array(
        "Arial", "Tahoma", "Verdana", "Geneva", "Helvetica", "Lucida Sans", "Trebuchet", 
        "Times New Roman", "Georgia", 
        "Courier New", "Courier", "Lucida Console", "Monaco", "Segoe UI Light", "Segoe UI"
    );

    if (is_array($wpj->general_settings['fonts'])) {
        $fonts = array_merge($fonts, $wpj->general_settings['fonts']);
        sort($fonts);    
    }
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>Johnson Box Wizard</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />    
    <link rel="stylesheet" href="css/shortcodes.css" />
    <link rel="stylesheet" href="<?php echo $wpj->get_plugin_uri(); ?>/assets/css/style.css" />
    <script src="<?php echo $url; ?>/wp-includes/js/jquery/jquery.js"></script>
    <script src="<?php echo $url; ?>/wp-includes/js/tinymce/tiny_mce_popup.js"></script>
    <script src="js/jquery.tabify.js"></script>
    <script>
        var content = tinyMCEPopup.getWindowArg("content", ""),
            johnsonBox = {
                init: function(e) {
                    this.parseContent(content);
                    tinyMCEPopup.resizeToInnerSize();                    
                },
                parseContent : function(c){
                    var scOpenTag = c.match(/\[cow_johnson.+?\]/);
                    if (scOpenTag instanceof Array) {
                        content = c.replace(/^[^\]]+]|\[\/cow_johnson\].*$/g, "");
                        var params = scOpenTag[0].match(/\w+="[^"]*"/g),
                            param = [];                        
                        for (var i = 0; i < params.length; i++){
                            param = params[i].split("=");
                            if (document.getElementById(param[0]))                               
                                document.getElementById(param[0]).value = param[1].replace(/"/g, "");    
                        }        
                    }                           
                },
                pickColor : function(elem) {
                    tinyMCEPopup.pickColor(this.e, elem);    
                },
                generateOutput : function(c, p) { // c: Content, p: Preview?                    
                    var general_float               = jQuery('#general_float').val(),
                        general_clear               = jQuery('#general_clear').val(),
                        general_link                = jQuery('#general_link').val(),                        
                        general_width               = jQuery('#general_width').val(),
                        general_bgcolor             = jQuery('#general_bgcolor').val(),
                        general_bgcolor_to          = jQuery('#general_bgcolor_to').val(),
                        general_color               = jQuery('#general_color').val(),
                        general_shadowcolor         = jQuery('#general_shadowcolor').val(),
                        general_font                = jQuery('#general_font').val(),
                        general_fontsize            = jQuery('#general_fontsize').val(),
                        general_lineheight          = jQuery('#general_lineheight').val(),
                        general_gradientstyle       = jQuery('#general_gradientstyle').val();

                    // Start generating the shortcode    
                    var output = '[cow_johnson';
                   
                    if(general_float && general_float != 'left') {
                        output += ' general_float="' + general_float + '"';
                    }
                    
                    if(general_clear && general_clear != 'both') {
                        output += ' general_clear="' + general_clear + '"';
                    }

                    if(general_link) {
                        output += ' general_link="' + general_link + '"';
                    }
                                        
                    if(general_width) {
                        output += ' general_width="' + general_width + '"';
                    }

                    if(general_bgcolor && general_bgcolor != '') {
                        output += ' general_bgcolor="' + general_bgcolor + '"';
                    }
                    
                    if(general_bgcolor_to) {
                        output += ' general_bgcolor_to="' + general_bgcolor_to + '"';
                    }
                    
                    if(general_color && general_color != '#222222') {
                        output += ' general_color="' + general_color + '"';
                    }  
                    
                    if(general_shadowcolor) {
                        output += ' general_shadowcolor="' + general_shadowcolor + '"';
                    }
                    
                    if (p) {
                        if (general_font)
                            output += ' general_font="' + encodeURIComponent(general_font) + '"';    
                    }
                    else {
                        if (general_font && general_font != 'Arial')
                            output += ' general_font="' + general_font + '"';
                    }

                    if(general_fontsize && general_fontsize != 14) {
                        output += ' general_fontsize="' + general_fontsize + '"';
                    } 
                    
                    if(general_lineheight && general_lineheight != 20) {
                        output += ' general_lineheight="' + general_lineheight + '"';
                    } 
                    
                    if(general_gradientstyle && general_gradientstyle != 'diagonal_down') {
                        output += ' general_gradientstyle="' + general_gradientstyle + '"';
                    }
  
                    output += ']' + c + '[/cow_johnson]';
                    
                    return output;    
                },
                preview : function() {
                    var output = johnsonBox.generateOutput(content, true),
                        container = jQuery('#preview');
  
                    jQuery.ajax({
                        type: "POST",
                        url: "<?php echo $url; ?>/wp-admin/admin-ajax.php",
                        data: {
                            action : 'wpj_preview_cb',
                            content : output 
                        },                    
                        success: function(data){
                            container.html(data).fadeIn(400);
                        }
                    });     
                },
                insert: function() {
                    tinyMCEPopup.execCommand('mceReplaceContent', false, johnsonBox.generateOutput(content, false));
                    tinyMCEPopup.close();
                }
            }
        tinyMCEPopup.onInit.add(johnsonBox.init, johnsonBox);
    </script>
</head>
<body>
    <form action="#">
        <h3 class="page-title">Insert a Johnson Box</h3>
        <div id="shorcode-manager">

            <div id="general">
                
                <div class="table-row">
                    <label for="general_float">Location:</label>
                    <select id="general_float">
                        <option value="center" >Center</option>
                        <option value="left" selected="selected">Left</option>                    
                        <option value="right" >Right</option>                                                                                                                                                                                                                                                                                                                                                                                           
                    </select>            
                </div>
                
                <div class="table-row table-row-alternative">
                    <label for="general_clear">Clear:</label>
                    <select id="general_clear">
                        <option value="both" selected="selected">No floating elements allowed on either left or right side</option>
                        <option value="left">No floating elements allowed on the left side</option>                    
                        <option value="none" >Allow floating elements on both sides</option>                                                                                                                                                                                                                                                                                                                                                                                           
                        <option value="right" >No floating elements allowed on the right side</option>                                                                                                                                                                                                                                                                                                                                                                                           
                    </select>            
                </div>
                
                <div class="table-row">
                    <label for="general_link">Link To:</label>
                    <input class="input-long" type="text" id="general_link" value="" title="Specify a destination for the box link" />           
                </div>
                 
                <div class="table-row table-row-alternative">
                    <label for="general_width">Width:</label>
                    <input type="text" class="input-mini" id="general_width" value="" /><span>px</span>            
                </div>
                
                <!--@todo: als achtergrond niet nodg is, blijft gradientstyle op oude waarde staan. Moet dan niet in de shortcode komen!!!-->
                
                <div class="table-row">
                    <label for="general_bgcolor">Background Color:</label>
                    <input type="text" class="cow_colorpicker input-mini" id="general_bgcolor" />
                    <input type="text" class="cow_colorpicker input-mini" id="general_bgcolor_to" title="Specify a stop color for the gradient" /> 
                    <select id="general_gradientstyle">
                        <option value="horizontal">Horizontal</option>
                        <option value="vertical">Vertical</option>                    
                        <option value="diagonal_down" selected="selected">Diagonal (down)</option>
                        <option value="diagonal_up">Diagonal (up)</option>
                        <option value="radial">Radial</option>                                                                                                                                                                                   
                    </select>                 
                </div>
                
                <div class="table-row">
                    <label for="general_font">Font Family:</label>
                    <select id="general_font">
                        <?php foreach($fonts as $font): ?>
                        <option value="<?php echo $font; ?>"><?php echo $font; ?></option>
                        <?php endforeach; ?>                                                                                                                                                               
                    </select>            
                </div>
                
                <div class="table-row table-row-alternative">
                    <label for="general_color">Font Color:</label>
                    <input type="text" class="cow_colorpicker input-mini" id="general_color" value="#222222" title="Font color" />
                    <input type="text" class="cow_colorpicker input-mini" id="general_shadowcolor" title="Specify a color for the text shadow" />            
                </div>
                
                <div class="table-row">
                    <label for="general_fontsize">Font Size:</label>
                    <input type="text" class="input-mini" id="general_fontsize" value="14" /><span>px</span>            
                </div>
                
                <div class="table-row table-row-alternative">
                    <label for="general_lineheight">Line Height:</label>
                    <input type="text" class="input-mini" id="general_lineheight" value="20" /><span>px</span>            
                </div>
                    
            </div><!--End of General-->

            <div class="table-row table-row-last">            
                <input type="button" class="cta_button" value="Close" onclick="tinyMCEPopup.close();" />
                <input type="button" class="cta_button" value="Preview" onclick="johnsonBox.preview();" />
                <input type="submit" class="cta_submit" value="Insert" onclick="johnsonBox.insert();" />                 
            </div>
                         
        </div><!--End of shortcode-manager-->    
    </form>

    <div id="preview">
        
    </div>
    
    <script>
        jQuery(function() {
            
            // Activate popup window color pickers
            jQuery('.cow_colorpicker').on('click', function(){
                johnsonBox.pickColor(jQuery(this).attr('id'));    
            });
            
            // Fade out the JB preview window
            jQuery('#preview').on('click', function(){
                jQuery(this).fadeOut(400);
            });
                  
        });
    </script>
    
</body>
</html>
