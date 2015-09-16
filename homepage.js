$(document).ready(function() {
  //$('#query').focus();
  var params = getParams();
  
  if(params.q) {
    $("#query").val(params.q);
    getResults();
  }

});

function getResults(){
	var homeUrl = window.location.href.replace(window.location.search,'');
	$('.search-results').fadeIn();
	$('#search-results-back').attr('href', homeUrl);
	$('.link-grid').fadeOut();
	$('.throbber-loader').show();
	query = $("#query").val();
	queryPlus = query.replace(' ', '+');
	$.getJSON(web_base + "/api/hollis/" + queryPlus + "?callback=?", function(data) {
    if(data.results.length > 0) { 
    	if(data.results.length < data.totalResults) {
    		data.term = query;
    	}
    	var source = $("#catalog-template").html();
    	var template = Handlebars.compile(source);
    	$('#catalog').html(template(data));
    }
    else {
    	var source = $("#catalog-template").html();
    	var template = Handlebars.compile(source);
    	$('#catalog').html(template({alert: 'No results'}));
    }
  });
  $.getJSON(web_base + "/api/law-libguides/" + query + "?callback=?", function(data) {
    if(data.results.length > 0) {
    	if(data.results.length < data.totalResults) {
    		data.term = query;
    	}
    	var source = $("#libguides-template").html();
    	var template = Handlebars.compile(source);
    	$('#libguides').html(template(data));
    }
    else {
    	var source = $("#libguides-template").html();
    	var template = Handlebars.compile(source);
    	$('#libguides').html(template({alert: 'No results'}));
    }
  });
  $.getJSON(web_base + "/api/libanswers/" + query + "?callback=?", function(data) {
  	if(data.results.length > 0) {
  		if(data.results.length < data.totalResults) {
    		data.term = query;
    	}
    	var source = $("#answers-template").html();
    	var template = Handlebars.compile(source);
    	$('#answers').html(template(data));
    }
    else {
    	var source = $("#answers-template").html();
    	var template = Handlebars.compile(source);
    	$('#answers').html(template({alert: 'No results'}));
    }
  });
}

function getParams() {
	    var vars = [], hash;

        var hashes = window.location.href.slice(inArray('?', window.location.href) + 1).split('&');

	    // create array for each key
	    for(var i = 0; i < hashes.length; i++) {
	    	hash = hashes[i].split('=');
	    	vars[hash[0]] = [];
	    }

	    // populate newly created entries with values 
	    for(var i = 0; i < hashes.length; i++) {
	        hash = hashes[i].split('=');
	        if (hash[1]) {
	        	vars[hash[0]].push(decodeURIComponent(hash[1].replace(/\+/g, '%20')));
	        }
	    }

	    return vars;
}

function inArray( elem, array ) {
        if ( array.indexOf ) {
            return array.indexOf( elem );
        }

        for ( var i = 0, length = array.length; i < length; i++ ) {
            if ( array[ i ] === elem ) {
                return i;
            }
        }
        return -1;
    }