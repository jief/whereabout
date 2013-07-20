$(document).ready(function(){
	$('#where-form').validate();

	$('a.delete').click(function() {
		return confirm('Sure ?');
	});  
});