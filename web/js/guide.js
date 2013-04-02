$(document).ready(function() {
 $.getJSON("http://hlsl7.law.harvard.edu/dev/annie/research/guide" + guide + ".json", function(sections) {
    var source = $("#guide-template").html();
    var template = Handlebars.compile(source);
    $('#guide').append(template(sections));
  });});