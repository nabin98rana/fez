
/**
 * Provides suggestions for state names (USA).
 * @class
 * @scope public
 */
function StateSuggestions(class_name, method, show_all, include_name) {
    this.mutex = 0;
    this.sugg = new Suggestor();
    this.sugg.class_name = class_name; // target class name
    this.sugg.method = method; // method to call on the target class to get suggestions
    this.sugg.show_all = show_all;
    this.sugg.include_name = include_name;
    this.sugg.ongetSuggestionError = function() {
        alert("getSuggestionError");
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
        bTypeAhead /*:boolean*/       
        ) {

    // debounce the keypresses so we don't do a search for everykeypress - just when the user stops typing
    date1 = new Date();
    this.keyTime = date1.getTime();
    oSuggestor = this;
    oAutoSuggestControl_local = oAutoSuggestControl;
    bTypeAhead_local = bTypeAhead;
    setTimeout("oSuggestor.requestSuggestions2(oAutoSuggestControl_local,bTypeAhead_local)",300);
};


StateSuggestions.prototype.requestSuggestions2 = function (oAutoSuggestControl /*:AutoSuggestControl*/,
        bTypeAhead /*:boolean*/
        ) {

    // Don't do search until user has stopped typing.
    date1 = new Date();
    keydelay = date1.getTime() - this.keyTime;
    var aSuggestions = [];
    var sTextboxValue = oAutoSuggestControl.textbox.value;
    var sTextboxName = oAutoSuggestControl.textbox.name;

    if (keydelay < 280) {
      return;
	}
	
    // only run for user input at least 2 characters
    if (sTextboxValue.length < 2) {
    	return;
    }
	
    // only do one search
    if (this.mutex++ > 0) {
        this.mutex--;
        return;
    }
    var aSuggestions = [];
    var sTextboxValue = oAutoSuggestControl.textbox.value;
    var sTextboxName = oAutoSuggestControl.textbox.name;


    s = document.getElementById(sTextboxName+'_searching');
    if (s) {
        s.style.display = '';
    }
    oSuggestor = this;
    this.sugg.onGetSuggestionError = function() {
        oAutoSuggestControl_local = oAutoSuggestControl;
        bTypeAhead_local = bTypeAhead;
        s = document.getElementById(sTextboxName+'_searching');
        if (s) {
            s.style.display = 'none';
        }
        // retry a few times
        if (oSuggestor.err_count++ < 3) {
            setTimeout("oSuggestor.requestSuggestions2(oAutoSuggestControl_local,bTypeAhead_local)",500);
        } else {
            oSuggestor.err_count = 0;
        }
        oSuggestor.mutex--;
    }
    this.sugg.getSuggestion(sTextboxValue, function(suggest_list) {
            s = document.getElementById(sTextboxName+'_searching');
            if (s) {
              s.style.display = 'none';
            }
            aSuggestions = suggest_list;
//			alert('key delay = '+keydelay+'. val = '+sTextboxValue);
            //provide suggestions to the control
            // never allow type ahead as we are suggesting using a search that 
            // might match partway through a string
            oAutoSuggestControl.autosuggest(aSuggestions, false);
            oSuggestor.mutex--;
            });

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
//		alert(sTextboxValue);
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


