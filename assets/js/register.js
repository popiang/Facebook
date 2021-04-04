$(document).ready(function() {

	$("#register_button").click(function() {
		alert('masuk');
		$("#first").hide();
		$("#second").show();
	});

	// on click signup, hide login form and show registration form
	$("#signup").click(function() {

		$("#first").slideUp("slow", function() {

			$("#second").slideDown("slow");

		});

	});

	// on click signup, hide registration form and show login form
	$("#signin").click(function() {

		$("#second").slideUp("slow", function() {

			$("#first").slideDown("slow");

		});

	});	

});