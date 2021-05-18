 (function ($) {
  $(document).ready(function () {

   $('.field--name-field-department-contact-data').attr('id', 'department_contact_data');
   $('.field--name-field-researches-publications').attr('id', 'department_researches_publications');
   $('.field--name-field-dep-undergraduates').attr('id', 'department_undergraduates');
   $('.field--name-field-dep-postgraduates').attr('id', 'department_postgraduates');
   $('.department-block .views-field-field-department-image .field-content').attr('id', 'department_image');
   $("<div class='overlay'>  </div>" ).insertBefore( ".department-block .views-field-field-department-image .field-content" );
   $("<div class='overlay'>  </div>" ).insertBefore( ".home-news-block .views-field-field-news-image .field-content" );
   $("<i class='fa fa-clock-o'></i>" ).insertBefore( ".field--name-field-research-date time" );
   $("<i class='fa fa-clock-o'></i>" ).insertBefore( ".field--name-field-award-date time" );
   $("<i class='fa fa-clock-o'></i>" ).insertBefore( ".field--name-field-job.field--type-datetime time" );
   var page_url=  window.location.href;
   if (page_url.includes('department/projects')) { 
     $('#department_projects_link').attr('class', 'menu-item--active-trail');
   }else if(page_url.includes('department/graduates')){
	 $('#department_graduates_link').attr('class', 'menu-item--active-trail');
   }else if(page_url.includes('department/postgraduates')){
	 $('#department_postgraduates_link').attr('class', 'menu-item--active-trail');
   }else if(page_url.includes('department/undergraduates')){
	 $('#department_undergraduate_link').attr('class', 'menu-item--active-trail');
   }else if(page_url.includes('department/research')){
	 $('#department_resharch_link').attr('class', 'menu-item--active-trail');
   }else if(page_url.includes('department/staff')){
	 $('#department_staff_link').attr('class', 'menu-item--active-trail');
   }else if(page_url.includes('department/about')){
	 $('#department_about_link').attr('class', 'menu-item--active-trail');
   }else{
	 $('#department_overview_link').attr('class', 'menu-item--active-trail');
   }

   $('.js-form-type-search').attr('id', 'js-form-type-search');
   var html_code = '<i class="fa fa-search"></i>';
   document.getElementById("js-form-type-search").innerHTML +=  html_code;

//   $('.').attr('id', '');

    // === ColorPicker ===
    if($.fn.ColorPicker){
      $('input.color-picker').each(function(){
        var $input = $(this);
        var name = $input.attr('name');
        $( "<span class=\"color-check color-"+name+"\"></span>" ).insertAfter( $input );
        $input.ColorPicker({
          onChange:function (hsb, hex, rgb) {
           $('span.color-' + name).css('backgroundColor', '#' + hex);
            $input.val( '#' + hex );
          }
       });
      });
    };

    $('input.color-picker').on('change', function(){
      var name = $(this).attr('name');
      $('span.color-' + name).css('backgroundColor', $(this).val());
    });

    $('input.color-picker').each(function(){
      $color = $(this).val();
      $name = $(this).attr('name');
      $('span.color-' + $name).css('backgroundColor', $color);
    });
	
	
    $('.home_page_search .bb-container').attr('class', 'container');
    var html_code = "<option value ='All'>None</option>";
	$('#edit-field-category-type-value-1').html(html_code);
    $('#edit-field-category-type-value').on('change', function(){
      $category_value = $('#edit-field-category-type-value').val();
      //$.get(drupalSettings.path.baseUrl +'get/category/links?id='+ $category_value, null, function(response) {
      var lang = document.documentElement.lang;
      $.get(drupalSettings.path.baseUrl +'get/category/links?id='+ $category_value+'&lang='+lang, null, function(response) {      
        $('#edit-field-category-type-value-1').html(response);
      });
	  if($category_value == 'All'){
		var html_code = "<option value ='All'>None</option>";
		$('#edit-field-category-type-value-1').html(html_code);
	  }
    });
	
	
    $('#edit-field-category-type-value-1').on('change', function(){
      $category_link = $('#edit-field-category-type-value-1').val();
	  if($category_link !== 'All'){
	    window.open($category_link);
	  }
    });
	$('#edit-submit-fast-search').click(function(){
		$url_value = $('#edit-field-category-type-value-1').val();
		if($url_value !== 'All'){
		  window.open($url_value);
		}
    });
	$('.home_page_search .form-actions').attr('id', 'fast_Search_div');
      document.getElementById("fast_Search_div").innerHTML =  "";
//document.getElementById('edit-submit-fast-search').type = 'hidden';
  });


  $(window).load(function(){

    $('input.color-picker').each(function(){
      $color = $(this).val();
      $name = $(this).attr('name');
      $('span.color-' + $name).css('backgroundColor', $color);
    });
  })

  $(document).ready(function(){
    function getByClass(sClass){
      var aResult=[];
      var aEle=document.getElementsByTagName('*');
      for(var i=0;i<aEle.length;i++){
        /*foreach className*/
        var arr=aEle[i].className.split(/\s+/);
        for(var j=0;j<arr.length;j++){
          /*check class*/
          if(arr[j]==sClass){
            aResult.push(aEle[i]);
          }
        }
      }
      return aResult;
    };


    function runRender(type){
      var aBox = getByClass("code_"+type);
      for(var i=0;i < aBox.length; i++){
        var editor = false;
        if(!editor){
          editor = CodeMirror.fromTextArea(aBox[i], {
            lineNumbers: true,
            mode: "text/css",
          });
        }
        editor.on("blur", function() {editor.save()});
      }
    };

    runRender('css');
    $('a[href="#edit-css-customize"]').click(function(){
      runRender('css');
    })
  })

})(jQuery);

