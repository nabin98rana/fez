function copy_field_text(dest, value)
{
	f = document.forms.wfl_form1;
	fe = getFormElement(f, dest);
	if (fe != null) {
		fe.value = value;
	}
}

function copy_field_date(dest,value)
{
	f = document.forms.wfl_form1;
	selectOption(f, dest + '[Month]', value.substr(5,2));
	selectOption(f, dest + '[Day]', parseInt(value.substr(8,2))).toString;  // remove leading zero
	selectOption(f, dest + '[Year]', value.substr(0,4));
}