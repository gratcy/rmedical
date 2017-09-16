var cal_obj2 = null;

var format = '%Y-%m-%d';

// show calendar
function show_cal(el, input_field) {

	if (cal_obj2) return;

	var text_field = document.getElementById(input_field);
	if (text_field == null)
		text_field = document.forms[0].elements.namedItem(input_field);
	if (text_field == null)
		text_field = document.forms[1].elements.namedItem(input_field);

	cal_obj2 = new RichCalendar();
	cal_obj2.start_week_day = 0;
	cal_obj2.show_time = false;;
	cal_obj2.language = 'en';
	cal_obj2.user_onchange_handler = cal2_on_change;
	cal_obj2.user_onclose_handler = cal2_on_close;
	cal_obj2.user_onautoclose_handler = cal2_on_autoclose;

	cal_obj2.parse_date(text_field.value, format);

	cal_obj2.show_at_element(text_field, "adj_left-bottom");
	cal_obj2.change_skin('');
	
	cal_obj2.input_field	= text_field;

}

// user defined onchange handler
function cal2_on_change(cal, object_code) {
	if (object_code == 'day') {
		cal_obj2.input_field.value = cal.get_formatted_date(format);
		cal.hide();
		cal_obj2 = null;
	}
}

// user defined onclose handler (used in pop-up mode - when auto_close is true)
function cal2_on_close(cal) {
	cal.hide();
	cal_obj2 = null;
}

// user defined onautoclose handler
function cal2_on_autoclose(cal) {
	cal_obj2 = null;
}