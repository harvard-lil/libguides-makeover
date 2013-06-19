$(document).ready(function() {
 $.getJSON("http://hlsl7.law.harvard.edu/dev/annie/research/guide103327.json", function(sections) {
    var source = $("#guide-template").html();
    var template = Handlebars.compile(source);
    $('#guide').append(template(sections));
  });});