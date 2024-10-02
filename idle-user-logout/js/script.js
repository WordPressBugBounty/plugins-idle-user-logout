if(iul.is_mobile){
  jQuery(document).ready(function(){
    main_script();
  });
}else{
  jQuery(window).on('load',function(){
    main_script();
  });
}


function main_script(){
  if( typeof(iul) != 'undefined' ){
    var content = iul.actions.modal,
        timer = parseInt(iul.actions.timer)*1000,
        action = iul.actions.action_type,
        action_value = iul.actions.action_value,
        disable_admin = iul.actions.disable_admin,
        popup_open = 0;

    if(!disable_admin){
      jQuery(document).idleTimer(timer);
      

      jQuery(document).bind("active.idleTimer", function(){
        idle_user_status_call('active');
      });

      
      jQuery(document).bind("idle.idleTimer", function(){
        idle_user_status_call('idle');
      });

      jQuery(document).bind("idle.idleTimer", function(){
          switch(parseInt(action)){
            case 2:
              idle_user_logout_callback('',true);
            break;

            case 3:
              if(action_value){
                idle_user_logout_callback(action_value,false);
              }
            break;

            case 4:
              console.log(popup_open);
              if (content && popup_open==0){
                var modal = UIkit.modal.blockUI(content);
                popup_open = 1;
                jQuery('#close_modal').on('click',function(e){
                  e.preventDefault();
                  modal.hide();
                  popup_open = 0;
                });
              }
            break;

            case 5:
              if(action_value){
                window.location = action_value;
              }
            break;

            case 1:
            default:
            /* Do nothing */
          }
      });
    }
  } 
}

function idle_user_logout_callback(url,reload){
    jQuery.ajax({  
      type: 'POST',
      url: iul.ajaxurl,
      data: {action: 'logout_idle_user'},    
      success: function(response){ 
        if( response.trim() ==  "true" ){
          jQuery(window).unbind();
          if(reload){
            location.reload();
          }else{
            window.location = url;
          }
        }
      },  
      error: function(MLHttpRequest, textStatus, errorThrown){ console.log(errorThrown); }  
    });
}


function idle_user_status_call(type){
  jQuery.ajax({  
    type: 'POST',
    url: iul.ajaxurl,
    data: {action: 'update_user_time', callType:type},
    error: function(MLHttpRequest, textStatus, errorThrown){ console.log(errorThrown); },
    success: function(response){ console.log(response); }
  });
  return null;
}
