
$(document).ready(function() {

	$('#gettext_parser').change(function() {
		if(this.value == 'po') {
			$('#gettext_options').fadeOut();
		} else {
			$('#gettext_options').fadeIn();
		}
	});
	
})

