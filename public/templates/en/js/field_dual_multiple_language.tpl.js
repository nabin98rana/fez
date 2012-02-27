{*
 * Binds the Language field trigger with Translated field(s) event handler.
 * Handles the toggling on Translated fields.
 *}
<script type="text/javascript">
<!-- 
    {literal}
    
    /**
     * Display Translated fields 
     * when there is a language other than English selected on the Language field
     */
    function translated_fields_trigger(showHint){
        var languages = $("#{/literal}{$field_id}{literal} option");
        for (c = 0; c < languages.length; c++){
            if (languages[c]['value'] != "eng"){
                // Show translated fields
                var translated_fields = $("TR.translated_field");
                translated_fields.show();

                // Show translated hint   
                if(showHint){
                    var hint = '<span class="gotoTranslated" onclick="scrollToField(\'' + translated_fields[0].id + '\')"> ' +
                                'Click here to scroll to Title fields.' + 
                           '</span>';
                    $("#translated_hint").html(hint);
                    $("#translated_hint").fadeIn();
                }
                
                // Nothing else to do here.    
                break;
            }
        }
    }
    
    
    /**
     * Scroll page to specified element.
     */
    function scrollToField(field_id){
        field = $("#" + field_id);
        $("html, body").animate(
            {scrollTop: field.offset(0).top},
            'slow', 
            'swing',
            function(){
                $(field).focus();
            }
        );
    }
            
    
    /**
     * On DOM Ready:
     * Toggle Translated fields.
     * Assign handler for toggling Translated fields.
     */
    $(function(){
        // Hide all Translated field(s). 
        // For New record creation, we don't need the translated fields until they are triggered by Language field.
        $("TR.translated_field").hide();
        
        // Initial call to trigger Translated fields for Edit form
        if ({/literal}{$initialTrigger}{literal}){
            translated_fields_trigger(false);
        }

        // Assign click handler for the Language combo buttons
        var btn_remove_left = "{/literal}{$btn_remove_left}{literal}";
        var btn_copy_left = "{/literal}{$btn_copy_left}{literal}";

        $("#"+btn_copy_left).click(function(){
            translated_fields_trigger(true)
        });
    });
    
    
    {/literal}
-->
</script>
