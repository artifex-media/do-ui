import $ from 'jquery';
window.jQuery = window.$ = $;

import 'jquery-ui/dist/jquery-ui';
import 'bootstrap';

import './js/bootstrap-datetimepicker';
import './js/jscolor';


// import {SlimSelect} from "./node_modules/slim-select/dist/slimselect.es.js";

import Swal from 'sweetalert2'
import SlimSelect from 'slim-select'

// export Swal from "sweetalert2"
// export {slim_select as SlimSelect}

// export { Swal, SlimSelect }
 

const fixHelper = function(e, ui) {  
    ui.children().each(function() {  
      $(this).width($(this).width());  
    });  
    return ui;  
  };

// copyToClipboard
const copyToClipboard = function(selector,button = false){
    const textToCopy = selector.attr('data-copy-value');

    if(button) {
        document.querySelector(selector).parentElement.classList.add('button__loading');
    }
    navigator.clipboard.writeText(textToCopy).then(
        function() {
            /* clipboard successfully set */
            Swal.fire({
            icon: 'success',
            title: 'Copied to clipboard',
            });
        },
        function() {
            /* clipboard write failed */
        }
    );
}
export{copyToClipboard}

function defer(method) {
    if (window.jQuery) {
        method();
    } else {
        setTimeout(function() { defer(method) }, 50);
    }
}

// All
const init = function(){

    console.log('test');
    
    // window.$ = window.jQuery = require('jquery');
    
    $(document).ready(function() {
    $.widget.bridge('uitooltip', $.ui.tooltip);

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });


    // Hashed url with Bootstrap
    var hash = location.hash.replace(/^#/, '');
    
    if (hash) {

        var NavTabs = $('.nav-tabs a[href="#' + hash + '"]');
        var NavTabsParent = NavTabs.parents('.nav-tabs');
        var NavTabsParentParent = NavTabsParent.parents('.tab-pane');
        if(NavTabsParentParent.length > 0) {
            var ParentID = NavTabsParentParent.attr('id');
            var NavTabsParentParentTab = $('.nav-tabs a[href="#' + ParentID + '"]');
            NavTabsParentParentTab.tab('show');
        }

        NavTabs.tab('show');
        // $('.nav-tabs a[href="#' + hash + '"]').tab('show');
        
        if($('input[name=tab_pane]').length == 0) {
            $('.nav-tabs').closest('form').prepend('<input type="hidden" name="tab_pane" value="'+hash+'"/>');
        }
    }

    $('.nav-tabs a').on('shown.bs.tab', function (e) {
        window.location.hash = e.target.hash;
        if($('input[name=tab_pane]').length == 0) {
            $('.nav-tabs').closest('form').prepend('<input type="hidden" name="ab_pane" />');
        }
        $('input[name=tab_pane]').val(e.target.hash.replace(/^#/, ''));
    });


    $('#navHamburger').on('click', function() {
      $('#sidebarContent').toggleClass('toggled');
  });

  
  $('.hamburger a').click(function(e){
    e.preventDefault();
    $('.hamburger-wrap,.sidebar-nav,.bottom').slideToggle();
});


$('._resource-action').click(function(e) {
    e.preventDefault();

    var This = $(this);
    var Action = $(this).attr('data-action');
    var ModelID = Action == 'deletemedia' ? $(this).attr('data-model-id') : $(this).parent().parent().attr('data-id');
    var Model = Action == 'deletemedia' ? $(this).attr('data-model') : $(this).parent().parent().attr('data-model');
    var recordRow = Action == 'deletemedia' ? $(this).parent() : $(this).parent().parent();
    var ModelImageCollection = $(this).attr('data-model-image-collection');
    var AjaxUrl = '/dashboard/resources/'+Action;
    var AjaxType = 'POST';

    // Rewrite Action (duplicate etc.) to Duplicate etc.
    var ActionLetter = Action.charAt(0);
    var ActionLetter = ActionLetter.toUpperCase()
    var ActionWord = Action.slice(1)
    var ActionWord = Action == 'deletemedia' ? 'Delete image' : ActionLetter + ActionWord;
    var ActionResponse = Action == 'deletemedia' ? 'delete' : Action;

    Swal.fire({
      title: 'Are you sure?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#38c172',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Yes, '+ActionResponse+' it!'
  }).then((result) => {
      // if (result.value) {
      if (result.isConfirmed) {
          $.ajax({
            url: AjaxUrl,
            type: AjaxType,
            data: 'model='+Model+'&id='+ModelID+'&action='+Action+'&modelImageCollection='+ModelImageCollection,
            success: function success(result) {
                console.log(result);
              if ($.trim(result) == 'true' || result == 'true') {
                  if(Action == 'delete' || Action == 'archive' || Action == 'restore' || Action == 'unarchive' || Action == 'deletemedia') {
                      recordRow.remove();
                    }
                    
                    if(Action == 'duplicate') {
                    window.location.reload();
                    // window.location.href=window.location.href;
                } else {
                  Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: ActionWord+' successfull!'
                  });
                }

              } else {
                Swal.fire({
                  icon: 'error',
                  title: 'Oops...',
                  text: 'Something went wrong!',
                  footer: 'Please contact your webmaster',
                });
              }
            },
            error: function error(jqXHR, exception) {
              Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Something went wrong!',
                footer: 'Please contact your webmaster',
              });
            }
          });
      }
  });
});


    // @copyToClipboard
    $(document).on('click','._copy-to-clipboard',function(e){
        e.preventDefault();

        var Selector = $(this);
        copyToClipboard(Selector);
    });

    
    //Slim select
    if ($('select.form-control').length > 0) {

        $('select.form-control').each(function(i, obj) {
            var This = $(this);
            var Select = [];
            var Multiple = $(this).attr('multiple');
            Select[i] = new SlimSelect({
                select: obj,
                searchingText: This.attr('data-searching'),
                showSearch: true,
                searchPlaceholder: This.attr('data-placeholder'),
                searchText: This.attr('data-noresults'),
                placeholder: 'Choose',
                settings: {
                    allowDeselect: (typeof Multiple !== 'undefined' && Multiple !== false),
                    closeOnSelect: !(typeof Multiple !== 'undefined' && Multiple !== false)
                }
            });
        });
    }


    $('[name=search]').keypress(function (e) {
        if (e.which == 13) {
            $(this).parent('form').submit();
            return false;    //<---- Add this line
        }
        });
    
        $('#navHamburger').on('click', function() {
            $('#sidebarContent').toggleClass('toggled');
        });

    // Reposition models
    $('.is-sortable').sortable({
    });

    $('.sortable tbody').sortable({
        'containment': 'parent',
        'helper': fixHelper,
        'revert': true,
        start: function(event, ui) {
            $(this).find('tr').each(function() {
                $(this).attr('data-old-position',$(this).index() + 1);
            });
        },
        update: function(event, ui) {
          
          var AjaxUrl = '/dashboard/resources/reposition';
          
          $(this).find('tr').each(function() {
              var Model = $(this).attr('data-model');
              var ModelID = $(this).attr('data-id');
                var thisID = $(this).attr('data-id');
                var newIndex = $(this).index() + 1;
                var oldIndex = $(this).attr('data-old-position');
                $(this).attr('data-new-position',newIndex);

                if(newIndex != oldIndex) {
                    $.ajax({
                        // url: '/dashboard/projects/reposition/'+thisID,
                        url: AjaxUrl,
                        type: 'POST',
                        data: 'model='+Model+'&id='+ModelID+'&action=reposition&oldPosition='+oldIndex+'&newPosition='+newIndex,

                        success: function(result) {
                            if ($.trim(result) == 'true') {
                            } else {
                                Swal.fire({
                                  icon: 'error',
                                  title: 'Oops...',
                                  text: 'Something went wrong!',
                                  footer: 'Please contact your webmaster and send the following code: CODE - FALSE-REPOSITION-'+model+'-'+thisID,
                              });
                            }
                        },
                        error: function(jqXHR, exception) {
                            Swal.fire({
                              icon: 'error',
                              title: 'Oops...',
                              text: 'Something went wrong!',
                              footer: 'Please contact your webmaster and send the following code: CODE-REPOSITION - '+jqXHR.status+' EXCEPTION - '+exception,
                          });
                        }
                    });
                }
            });

        }
    }).disableSelection();

});



// Other functions

    // Tooltip (bootstrap, must be included in app itself, not this ui)
    $('[data-toggle="tooltip"]').tooltip();


    // Datetimepicker (bootstrap, must be included in app itself, not this ui)
    $('.datetimepicker').datetimepicker({
         format: 'yyyy-mm-dd hh:ii:00',
    });

    // Datepicker, must be included in app itself, not this ui
    $( ".datepicker" ).datepicker({
        dateFormat: 'dd-mm-yy',
        firstDay: 1,
    });






  // CKEditor
  var elements = CKEDITOR.document.find('.has-editor'),
  i = 0,
  element;

while ((element = elements.getItem(i++))) {
  CKEDITOR.replace(element, {
      toolbarGroups: [{
              "name": "basicstyles",
              "groups": ["basicstyles"]
          },
          {
              "name": "links",
              "groups": ["links"]
          },
          {
              "name": "paragraph",
              "groups": ["list"]
          },
          {
              "name": "styles",
              "groups": ["styles"]
          },
          {
              "name": 'document',
              "groups": [ 'mode', 'document', 'doctools' ],
              // "items": [ 'Source']
          },
      ],
      height: 300,
      defaultLanguage: 'en',
      language: 'en',
      protectedSource: '/<i class[\s\S]*?\>/g',
      customConfig: '/js/ckeditor_config.js'
  });
  CKEDITOR.dtd.$removeEmpty.span = false;
CKEDITOR.dtd.$removeEmpty.i = false;
}

var elements2 = CKEDITOR.document.find('.has-editor-less-height'),
  i2 = 0,
  element2;

while ((element2 = elements2.getItem(i2++))) {
  CKEDITOR.replace(element2, {
      toolbarGroups: [{
        "name": "basicstyles",
        "groups": ["basicstyles"]
    },
    {
        "name": "links",
        "groups": ["links"]
    },
    {
        "name": "paragraph",
        "groups": ["list"]
    },
    {
        "name": "styles",
        "groups": ["styles"]
    },
    {
        "name": 'document',
        "groups": [ 'mode', 'document', 'doctools' ],
        // "items": [ 'Source']
    },
      ],
      height: 150,
      defaultLanguage: 'en',
      language: 'en',
      protectedSource: '/<i class[\s\S]*?\>/g',
      protectedSource: '/<\/i>/g )',
      customConfig: '/js/ckeditor_config.js'
      // extraPlugins: 'sourcedialog',
  });
  CKEDITOR.dtd.$removeEmpty.span = false;
  CKEDITOR.dtd.$removeEmpty.i = false;


}


}


defer(init);