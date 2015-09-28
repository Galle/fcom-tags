jQuery(document).ready(function($) {
        var selected =  $('#tag-select').val();
		var data = {
		'action': 'fcom_tags_select',
		'selected-tag': selected    // We pass php values differently!
	    };
	    // We can also pass the url value separately from ajaxurl for front end AJAX implementations
	    jQuery.post(ajax_object.ajax_url, data, function(response) {
		    $('#all-tag-select').find('option').remove().end();
		    $('#child-tag-select').find('option').remove().end();
		    var options = eval('(' + response + ')');
		    options['tags'].forEach(function(item){$('#all-tag-select').append('<option value='+item['term_id']+'>'+item['name']+'</option>');})
		    options['child_tags'].forEach(function(item){$('#child-tag-select').append('<option value='+item['term_id']+'>'+item['name']+'</option>');})
	    });
	});

$('#tag-select').on('change',
    function(){
        var data = {
		'action': 'fcom_tags_select',
		'selected-tag': this.value    // We pass php values differently!
	    };
	    // We can also pass the url value separately from ajaxurl for front end AJAX implementations
	    jQuery.post(ajax_object.ajax_url, data, function(response) {
		    $('#all-tag-select').find('option').remove().end();
		    $('#child-tag-select').find('option').remove().end();
		    var options = eval('(' + response + ')');
		    options['tags'].forEach(function(item){$('#all-tag-select').append('<option value='+item['term_id']+'>'+item['name']+'</option>');})
		    options['child_tags'].forEach(function(item){$('#child-tag-select').append('<option value='+item['term_id']+'>'+item['name']+'</option>');})
	    });
    }
);

$('#move-right').on('click',function(){
    var seleccionados = $("#all-tag-select option:selected").map(function(){ return this.value }).get().join(",");
    var selected =  $('#tag-select').val();
    var data = {
		'action': 'fcom_tags_right',
		'selected-tag': selected,
		'move-tags': seleccionados    // We pass php values differently!
	    };
    jQuery.post(ajax_object.ajax_url, data, function(response) {
        $('#all-tag-select').find('option').remove().end();
	    $('#child-tag-select').find('option').remove().end();
	    var options = eval('(' + response + ')');
	    options['tags'].forEach(function(item){$('#all-tag-select').append('<option value='+item['term_id']+'>'+item['name']+'</option>');})
	    options['child_tags'].forEach(function(item){$('#child-tag-select').append('<option value='+item['term_id']+'>'+item['name']+'</option>');})
    });
    
});

$('#move-left').on('click',function(){
    var seleccionados = $("#child-tag-select option:selected").map(function(){ return this.value }).get().join(",");
    var selected =  $('#tag-select').val();
    var data = {
		'action': 'fcom_tags_left',
		'selected-tag': selected,
		'move-tags': seleccionados    // We pass php values differently!
	    };
    jQuery.post(ajax_object.ajax_url, data, function(response) {
        $('#all-tag-select').find('option').remove().end();
	    $('#child-tag-select').find('option').remove().end();
	    var options = eval('(' + response + ')');
	    options['tags'].forEach(function(item){$('#all-tag-select').append('<option value='+item['term_id']+'>'+item['name']+'</option>');})
	    options['child_tags'].forEach(function(item){$('#child-tag-select').append('<option value='+item['term_id']+'>'+item['name']+'</option>');})
    });
    
});
