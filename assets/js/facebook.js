$(document).ready(function() {

	// button for profile post
	$('#submit_profile_post').click(function() {
		$.ajax({
			type: "POST",
			url: "includes/handlers/ajax_submit_profile_post.php",
			data: $('form.profile_post').serialize(),
			success: function(msg) {
				$("#post_form").modal('hide');
				location.reload();
			},
			error: function() {
				alert('Failure');
			}
		});
	});
});

// used during searching user in messages section to start a new conversation with
function getUser(value, user) {
	$.post("includes/handlers/ajax_friend_search.php", {query:value, userLoggedIn:user}, function(data) {
		$(".results").html(data);
	});
}

// used in all dropdown from menu in the header
// get data for the dropdown menu and then display the dropdown menu
function getDropdownData(user, type) {

	// toggle the dropdown menu
	if ($(".dropdown_data_window").css("height") == "0px") {

		let pageName;
		
		// choose between messages or notifications to set the right ajax file to call
		if (type == 'notification') {
			pageName = "ajax_load_notifications.php";
			$("span").remove("#unread_notification");
		} else if (type == 'message') {
			pageName = "ajax_load_messages.php";
			$("span").remove("#unread_message");
		}

		// ajax call
		let ajaxreq = $.ajax({
			url: "includes/handlers/" + pageName,
			type: "POST",
			data: "page=1&userLoggedIn=" + user,
			cache: false,

			success: function(response) {
				// set the result of the ajax call to the provided div
				$(".dropdown_data_window").html(response);
				// set the style so the dropdown will be visible
				$(".dropdown_data_window").css({"padding" : "0px", "height" : "280px", "border" : "1px solid #DADADA"});
				// indicator for the infinite loop function to know the type of the dropdown
				$("#dropdown_data_type").val(type);
			}
		});
	} else {
		$(".dropdown_data_window").html("");
		$(".dropdown_data_window").css({ "padding": "0px", "height": "0", "border" : "none"});
	}

}