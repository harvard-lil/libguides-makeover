$(document).ready(function() {
  var height = $(window).height() - 75;
  $('#my-nav ul').css('height', height);
 $.getJSON("http://hlslibappdev.law.harvard.edu/dev/annie/research/web/guides/guide" + guide + ".json", function(sections) {
    var source = $("#nav-template").html();
    var template = Handlebars.compile(source);
    $('#my-nav ul').append(template(sections));
    var source = $("#guide-template").html();
    var template = Handlebars.compile(source);
    $('.my-content').append(template(sections));
    $('.section').waypoint(function() {
      var href = $(this).attr('id'); console.log(href);
      $('.nav li').removeClass('active');
      $('a[href="#' + href + '"]').parent().addClass('active');
    }, { offset: 100 });
    $('.section:first').waypoint(function() {
      var href = $(this).attr('id'); console.log(href);
      $('.nav li').removeClass('active');
      $('a[href="#' + href + '"]').parent().addClass('active');
    });
  });
});