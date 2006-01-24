
/**
 * Provides suggestions for state names (USA).
 * @class
 * @scope public
 */
function StateSuggestions(class_name, show_all, include_name) {
    this.mutex = 0;
    this.sugg = new Suggestor();
    this.sugg.class_name = class_name;
    this.sugg.show_all = show_all;
    this.sugg.include_name = include_name;
    this.sugg.ongetSuggestionError = function() {
        //alert("getSuggestionError");
        this.mutex--;
    }
    this.sugg.onGetIdError = function() {
        alert("GetIdError");
        this.mutex--;
    }
    this.sugg.setTimeout(10000);
}

/**
 * Request suggestions for the given autosuggest control. 
 * @scope protected
 * @param oAutoSuggestControl The autosuggest control to provide suggestions for.
 */
StateSuggestions.prototype.requestSuggestions = function (oAutoSuggestControl /*:AutoSuggestControl*/,
                                                          bTypeAhead /*:boolean*/) {

	var aSuggestions = [];
    var sTextboxValue = oAutoSuggestControl.textbox.value;

    if (sTextboxValue.length > 0){
        if (this.mutex++ > 0) {
            najax.getXmlHttp().abort();
        }
        oSuggestor = this;
        this.sugg.getSuggestion(sTextboxValue, function(suggest_list) {				
				aSuggestions = suggest_list;
                oSuggestor.mutex--;
                //provide suggestions to the control
                oAutoSuggestControl.autosuggest(aSuggestions, false);
                });
    }

};

// insert the value from the suggest box to the multi select
StateSuggestions.prototype.addMulti = function (oAutoSuggestControl /*:AutoSuggestControl*/, multi) {
    // get the suggest value
    var sTextboxValue = oAutoSuggestControl.textbox.value;
    
    if (sTextboxValue.length > 0) {
        options_exists = false;
        for (var i = 0; i < multi.options.length; i++) {
            if (multi.options[i].text == sTextboxValue) {
                // option is already in the multiselect box
                oAutoSuggestControl.textbox.value = '';
                return;
            }
        }

        if (this.mutex++ > 0) {
            najax.getXmlHttp().abort();
        }
        // get the id for the text value
        oSuggestor = this;
        this.sugg.getId(sTextboxValue, function(value_id)
                {
                // add the option to the multi select and select it
                multi.options.length = multi.options.length + 1;
                multi.options[multi.options.length-1].text = sTextboxValue;
                multi.options[multi.options.length-1].value = value_id;
                multi.options[multi.options.length-1].selected = true;
                oAutoSuggestControl.textbox.value = '';
                oSuggestor.mutex--;
                }
                );
    }
};


