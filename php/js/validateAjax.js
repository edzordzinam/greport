    ValidateAjax = {
        initialize: function(formid, endpoint){
            end_url = endpoint;
            form_id = "#"+formid;
            form_idO = formid;

            $(form_id+" input:text").focusout(function(){
                var formElementID = $(this).attr("id");
                ValidateAjax.doValidate(formElementID);
                return false;
            });

            $(form_id+" input:password").focusout(function(){
                var formElementID = $(this).attr("id");
                ValidateAjax.doValidate(formElementID);
                return false;
            });

            $(form_id+" select").focusout(function(){
                var formElementID = $(this).attr("id");
                ValidateAjax.doValidate(formElementID);
                return false;
            });
        },

        doValidate: function(id){
            var url = end_url;
            var data = $(form_id).serialize();
            data += "&formName=" + form_idO;


            $("#"+id).parent().parent().removeClass("error");
            $.post(url,data,function(response){

                $("#"+id).parent().find(".help-inline").remove();
                if (response[id]){
                	$("#"+id).parent().parent().addClass("error");
                    $("#"+id).parent().append(ValidateAjax.getHTML(response[id]));
                }

                if (!$.isArray(response)){
                    $('#'+form_idO+' input[name="isValid"]').val(false);
                    $('input:submit').attr('disabled',true);
                }
                else{
                	$('#'+form_idO+' input[name="isValid"]').val(true);
                	$('input:submit').attr('disabled',false);
                }

            },"json");

        },
        getHTML: function(errArray){
            var o = "<span class='help-inline'>";
            $.each(errArray,function(key,value){
                o += value;
            });
            o+="</span>";
            return o;
        }
    };


