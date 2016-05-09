<?php 
if(!$hideTitle){
?>
<div class="table-header header-color-blue">
    <i class="icon-<?php echo $icon; ?>"></i>
    <?php echo $title; ?>
</div>
<?php
}    
    $this->widget(
        'TbDetailView', array(
        'data' => $model,
        'attributes' => array(
            array(
                'label' => Yii::t("D2filesModule.crud_static","Drop files to upload"),
                'type' => 'raw',
                'template' => $this->widget(
                    'vendor.ivarsju.d2files.widgets.d2Upload',
                    array(
                        'action' => 'template',
                        'model_name'=> $model_name,
                        //'model_name'=> Yii::app()->controller->id,
                        'model_id' => $model->getPrimaryKey(),
                        'controler' => $controller,
                        'readOnly' => $readOnly,
                        'widgetId' => $this->getId(),
                        ),
                    true
                    ),
                'value' => $this->widget("bootstrap.widgets.TbButton", array(
                    "icon"=>"icon-upload-alt no-margin",
                    'htmlOptions' => array(
                        'data-toggle' => 'tooltip',
                        'onclick' => '$("#fileupload_'.$this->getId().'").trigger("click");',
                        'title' => Yii::t("D2filesModule.crud_static","Add file"),
                        'class' => 'pull-right',
                     ),
                    'visible' => !$readOnly

                ),true)
                ,

            ),
        ),
    ));

