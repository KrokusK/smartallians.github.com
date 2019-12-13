<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>
<h1>Form for GET request</h1>

<?php $form = ActiveForm::begin(['id' => 'form-request-get', 'method' => 'get', 'action' => Yii::$app->urlManager->createUrl('request')]); ?>

    <?php echo $form->field($modelRequest, 'address')->input(['class' => 'form-control', 'value' => $modelRequest->address])->hint('Пожалуйста, заполните поле')->label('address'); ?>

    <?php echo $form->field($modelRequest, 'name')->input(['class' => 'form-control', 'value' => $modelRequest->address])->hint('Пожалуйста, заполните поле')->label('name'); ?>

    <?php echo $form->field($modelRequest, 'description')->input(['class' => 'form-control', 'value' => $modelRequest->address])->hint('Пожалуйста, заполните поле')->label('description'); ?>

<?php ActiveForm::end(); ?>

    <div class="form-group">
        <?= Html::submitButton('Submit', ['class' => 'btn btn-primary', 'id' => 'btn-request-get']) ?>
    </div>


<h1>Form for POST request</h1>

<?php $form = ActiveForm::begin(['id' => 'form-request-post', 'method' => 'post', 'action' => Yii::$app->urlManager->createUrl('request')]); ?>

    <?php echo $form->field($modelRequest, 'address')->input(['class' => 'form-control', 'value' => $modelRequest->address])->hint('Пожалуйста, заполните поле')->label('address'); ?>

    <?php echo $form->field($modelRequest, 'name')->input(['class' => 'form-control', 'value' => $modelRequest->address])->hint('Пожалуйста, заполните поле')->label('name'); ?>

    <?php echo $form->field($modelRequest, 'description')->input(['class' => 'form-control', 'value' => $modelRequest->address])->hint('Пожалуйста, заполните поле')->label('description'); ?>

<?php ActiveForm::end(); ?>

    <div class="form-group">
        <?= Html::submitButton('Submit', ['class' => 'btn btn-primary', 'id' => 'btn-request-post']) ?>
    </div>

<h1>Form for PUT request</h1>

<?php $form = ActiveForm::begin(['id' => 'form-request-put', 'method' => 'put', 'action' => Yii::$app->urlManager->createUrl('request')]); ?>

    <?php echo $form->field($modelRequest, 'address')->input(['class' => 'form-control', 'value' => $modelRequest->address])->hint('Пожалуйста, заполните поле')->label('address'); ?>

    <?php echo $form->field($modelRequest, 'name')->input(['class' => 'form-control', 'value' => $modelRequest->address])->hint('Пожалуйста, заполните поле')->label('name'); ?>

    <?php echo $form->field($modelRequest, 'description')->input(['class' => 'form-control', 'value' => $modelRequest->address])->hint('Пожалуйста, заполните поле')->label('description'); ?>

    <?php //echo Html::hiddenInput('nad', 'test') ?>

<?php ActiveForm::end(); ?>

    <div class="form-group">
        <?= Html::submitButton('Submit', ['class' => 'btn btn-primary', 'id' => 'btn-request-put']) ?>
    </div>

<h1>Form for DELETE request</h1>

<?php $form = ActiveForm::begin(['id' => 'form-request-delete', 'method' => 'delete', 'action' => Yii::$app->urlManager->createUrl('request')]); ?>

    <?php echo $form->field($modelRequest, 'address')->input(['class' => 'form-control', 'value' => $modelRequest->address])->hint('Пожалуйста, заполните поле')->label('address'); ?>

    <?php echo $form->field($modelRequest, 'name')->input(['class' => 'form-control', 'value' => $modelRequest->address])->hint('Пожалуйста, заполните поле')->label('name'); ?>

    <?php echo $form->field($modelRequest, 'description')->input(['class' => 'form-control', 'value' => $modelRequest->address])->hint('Пожалуйста, заполните поле')->label('description'); ?>

<?php ActiveForm::end(); ?>

    <div class="form-group">
        <?= Html::submitButton('Submit', ['class' => 'btn btn-primary', 'id' => 'btn-request-delete']) ?>
    </div>


    <!-- POPUP MODAL CONTACT -->
    <div class="modal inmodal contact" id="modalAlert" role="dialog" data-keyboard="false" data-backdrop="static">
        <div class="modal-dialog modal-md "></div>
    </div>

<?php
$script = <<< JS

   $(document).ready(function () { 
        $("#btn-request-get").on('click', function (event) { 
            //alert("test");

            event.preventDefault();            
            var form_data = new FormData($('#form-request-get')[0]);
            $.ajax({
                   url: $("#form-request-get").attr('action') + '?Request[address]=test&Request[name]=test&Request[description]=test', 
                   dataType: 'JSON',  
                   cache: false,
                   contentType: false,
                   processData: false,
                   //enctype: 'multipart/form-data',
                   data: form_data,//$(this).serialize(), 
                   //data: data, //$(this).serialize(),                     
                   type: 'get',                        
                   beforeSend: function() {
                       //alert("beforeSend");                       
                   },
                   success: function(response){                      
                       //alert("success");
                       //toastr.success(response.message);
                       //toastr["success"](response.message,response.status); 
                       //alert(response.message);
                       //message = response.message;
                       if (parseInt(response.status) == 1) {
                           inerHtmlMessage = "<div class=\"alert alert-success\" role=\"alert\">";
                           inerHtmlMessage += "<div class=\"modal-header\">";                           
                           inerHtmlMessage += "<button type=\"button\" class=\"close\" data-dismiss=\"modal\">&times;</button>";
                           inerHtmlMessage += "<h3 class=\"modal-title text-left\">Данные переданы!</h3>";
                           inerHtmlMessage += "</div>";
                           inerHtmlMessage += "<h4 class=\"text-center\">" + response.message + "</h4>";
                           inerHtmlMessage += "</div>";  
                           inerHtmlMessage += "<br>" + response.message;                           
                           $('#modalAlert').modal('show').find('.modal-dialog').html(inerHtmlMessage);
                           
                           //$('#addAppFormModel').modal('hide');
                       } else {
                           inerHtmlMessage = "<div class=\"alert alert-danger\" role=\"alert\">";
                           inerHtmlMessage += "<div class=\"modal-header\">";
                           inerHtmlMessage += "<button type=\"button\" class=\"close\" data-dismiss=\"modal\">&times;</button>";
                           inerHtmlMessage += "<h3 class=\"modal-title text-left\">Внимание!</h3>";
                           inerHtmlMessage += "</div>";
                           inerHtmlMessage += "<h4 class=\"text-center\">" + response.message + "</h4>";
                           inerHtmlMessage += "</div>";
                           $('#modalAlert').modal('show').find('.modal-dialog').html(inerHtmlMessage);
                           //$('#addAppFormModel').modal('hide');
                       }
                   },
                   complete: function() {
                       //alert("complete");
                   },
                   error: function (data) {
                      //toastr.warning("","There may a error on uploading. Try again later");    
                      //alert(response.message);
                   }
                });                
            return false;

        });
        
        
        $("#btn-request-post").on('click', function (event) { 
            //alert("test");

            event.preventDefault();            
            var form_data = new FormData($('#form-request-post')[0]);
            $.ajax({
                   url: $("#form-request-post").attr('action'), 
                   dataType: 'JSON',  
                   cache: false,
                   contentType: false,
                   processData: false,
                   enctype: 'multipart/form-data',
                   data: form_data, //$(this).serialize(), 
                   //data: data, //$(this).serialize(),                     
                   type: 'post',                        
                   beforeSend: function() {
                       //alert("beforeSend");                       
                   },
                   success: function(response){                      
                       //alert("success");
                       //toastr.success(response.message);
                       //toastr["success"](response.message,response.status); 
                       //alert(response.message);
                       //message = response.message;
                       if (parseInt(response.status) == 1) {
                           inerHtmlMessage = "<div class=\"alert alert-success\" role=\"alert\">";
                           inerHtmlMessage += "<div class=\"modal-header\">";                           
                           inerHtmlMessage += "<button type=\"button\" class=\"close\" data-dismiss=\"modal\">&times;</button>";
                           inerHtmlMessage += "<h3 class=\"modal-title text-left\">Данные переданы!</h3>";
                           inerHtmlMessage += "</div>";
                           inerHtmlMessage += "<h4 class=\"text-center\">" + response.message + "</h4>";
                           inerHtmlMessage += "</div>";  
                           inerHtmlMessage += "<br>" + response.message;                           
                           $('#modalAlert').modal('show').find('.modal-dialog').html(inerHtmlMessage);
                           
                           //$('#addAppFormModel').modal('hide');
                       } else {
                           inerHtmlMessage = "<div class=\"alert alert-danger\" role=\"alert\">";
                           inerHtmlMessage += "<div class=\"modal-header\">";
                           inerHtmlMessage += "<button type=\"button\" class=\"close\" data-dismiss=\"modal\">&times;</button>";
                           inerHtmlMessage += "<h3 class=\"modal-title text-left\">Внимание!</h3>";
                           inerHtmlMessage += "</div>";
                           inerHtmlMessage += "<h4 class=\"text-center\">" + response.message + "</h4>";
                           inerHtmlMessage += "</div>";
                           $('#modalAlert').modal('show').find('.modal-dialog').html(inerHtmlMessage);
                           //$('#addAppFormModel').modal('hide');
                       }
                   },
                   complete: function() {
                       //alert("complete");
                   },
                   error: function (data) {
                      //toastr.warning("","There may a error on uploading. Try again later");    
                      //alert(response.message);
                   }
                });                
            return false;

        });
        
        
        $("#btn-request-put").on('click', function (event) { 
            //alert("test");

            event.preventDefault();            
            var form_data = new FormData($('#form-request-put')[0]);
            $.ajax({
                   url: $("#form-request-put").attr('action'), 
                   dataType: 'JSON',  
                   cache: false,
                   contentType: false,
                   processData: false,
                   enctype: 'multipart/form-data',
                   data: {"Request[address]":"test","Request[name]":"test","Request[description]":"test"}, //$(this).serialize(), 
                   //data: data, //$(this).serialize(),                     
                   type: 'put',                        
                   beforeSend: function() {
                       //alert("beforeSend");                       
                   },
                   success: function(response){                      
                       //alert("success");
                       //toastr.success(response.message);
                       //toastr["success"](response.message,response.status); 
                       //alert(response.message);
                       //message = response.message;
                       if (parseInt(response.status) == 1) {
                           inerHtmlMessage = "<div class=\"alert alert-success\" role=\"alert\">";
                           inerHtmlMessage += "<div class=\"modal-header\">";                           
                           inerHtmlMessage += "<button type=\"button\" class=\"close\" data-dismiss=\"modal\">&times;</button>";
                           inerHtmlMessage += "<h3 class=\"modal-title text-left\">Данные переданы!</h3>";
                           inerHtmlMessage += "</div>";
                           inerHtmlMessage += "<h4 class=\"text-center\">" + response.message + "</h4>";
                           inerHtmlMessage += "</div>";  
                           inerHtmlMessage += "<br>" + response.message;                           
                           $('#modalAlert').modal('show').find('.modal-dialog').html(inerHtmlMessage);
                           
                           //$('#addAppFormModel').modal('hide');
                       } else {
                           inerHtmlMessage = "<div class=\"alert alert-danger\" role=\"alert\">";
                           inerHtmlMessage += "<div class=\"modal-header\">";
                           inerHtmlMessage += "<button type=\"button\" class=\"close\" data-dismiss=\"modal\">&times;</button>";
                           inerHtmlMessage += "<h3 class=\"modal-title text-left\">Внимание!</h3>";
                           inerHtmlMessage += "</div>";
                           inerHtmlMessage += "<h4 class=\"text-center\">" + response.message + "</h4>";
                           inerHtmlMessage += "</div>";
                           $('#modalAlert').modal('show').find('.modal-dialog').html(inerHtmlMessage);
                           //$('#addAppFormModel').modal('hide');
                       }
                   },
                   complete: function() {
                       //alert("complete");
                   },
                   error: function (data) {
                      //toastr.warning("","There may a error on uploading. Try again later");    
                      //alert(response.message);
                   }
                });                
            return false;

        });
        
        
                $("#btn-request-delete").on('click', function (event) { 
            //alert("test");

            event.preventDefault();            
            var form_data = new FormData($('#form-request-delete')[0]);
            $.ajax({
                   url: $("#form-request-delete").attr('action'), 
                   dataType: 'JSON',  
                   cache: false,
                   contentType: false,
                   processData: false,
                   enctype: 'multipart/form-data',
                   data: form_data, //$(this).serialize(), 
                   //data: data, //$(this).serialize(),                     
                   type: 'delete',                        
                   beforeSend: function() {
                       //alert("beforeSend");                       
                   },
                   success: function(response){                      
                       //alert("success");
                       //toastr.success(response.message);
                       //toastr["success"](response.message,response.status); 
                       //alert(response.message);
                       //message = response.message;
                       if (parseInt(response.status) == 1) {
                           inerHtmlMessage = "<div class=\"alert alert-success\" role=\"alert\">";
                           inerHtmlMessage += "<div class=\"modal-header\">";                           
                           inerHtmlMessage += "<button type=\"button\" class=\"close\" data-dismiss=\"modal\">&times;</button>";
                           inerHtmlMessage += "<h3 class=\"modal-title text-left\">Данные переданы!</h3>";
                           inerHtmlMessage += "</div>";
                           inerHtmlMessage += "<h4 class=\"text-center\">" + response.message + "</h4>";
                           inerHtmlMessage += "</div>";  
                           inerHtmlMessage += "<br>" + response.message;                           
                           $('#modalAlert').modal('show').find('.modal-dialog').html(inerHtmlMessage);
                           
                           //$('#addAppFormModel').modal('hide');
                       } else {
                           inerHtmlMessage = "<div class=\"alert alert-danger\" role=\"alert\">";
                           inerHtmlMessage += "<div class=\"modal-header\">";
                           inerHtmlMessage += "<button type=\"button\" class=\"close\" data-dismiss=\"modal\">&times;</button>";
                           inerHtmlMessage += "<h3 class=\"modal-title text-left\">Внимание!</h3>";
                           inerHtmlMessage += "</div>";
                           inerHtmlMessage += "<h4 class=\"text-center\">" + response.message + "</h4>";
                           inerHtmlMessage += "</div>";
                           $('#modalAlert').modal('show').find('.modal-dialog').html(inerHtmlMessage);
                           //$('#addAppFormModel').modal('hide');
                       }
                   },
                   complete: function() {
                       //alert("complete");
                   },
                   error: function (data) {
                      //toastr.warning("","There may a error on uploading. Try again later");    
                      //alert(response.message);
                   }
                });                
            return false;

        });
    });       

JS;
$this->registerJs($script);