<?php
$this->setPageTitle(
    Yii::t('D2filesModule.model', 'D2files Types')
    . ' - '
    . Yii::t('D2filesModule.crud_static', 'Manage')
);

?>

<div class="clearfix">
    <div class="btn-toolbar pull-left">
        <div class="btn-group">
        <?php 
        $this->widget('bootstrap.widgets.TbButton', array(
             'label'=>Yii::t('D2filesModule.crud_static','Create'),
             'icon'=>'icon-plus',
             'size'=>'large',
             'type'=>'success',
             'url'=>array('create'),
             'visible'=>(Yii::app()->user->checkAccess('D2files.D2filesType.*') || Yii::app()->user->checkAccess('D2files.D2filesType.Create'))
        ));  
        ?>
</div>
        <div class="btn-group">
            <h1>
                <i class=""></i>
                <?php echo Yii::t('D2filesModule.model', 'D2files Types');?>            </h1>
        </div>
    </div>
</div>

<?php Yii::beginProfile('D2filesType.view.grid'); ?>


<?php
$this->widget('TbGridView',
    array(
        'id' => 'd2files-type-grid',
        'dataProvider' => $model->search(),
        'filter' => $model,
        #'responsiveTable' => true,
        'template' => '{summary}{pager}{items}{pager}',
        'pager' => array(
            'class' => 'TbPager',
            'displayFirstAndLast' => true,
        ),
        'columns' => array(
            array(
                'class' => 'CLinkColumn',
                'header' => '',
                'labelExpression' => '$data->itemLabel',
                'urlExpression' => 'Yii::app()->controller->createUrl("view", array("id" => $data["id"]))'
            ),
            array(
                'class' => 'editable.EditableColumn',
                'name' => 'id',
                'editable' => array(
                    'url' => $this->createUrl('/d2files/d2filesType/editableSaver'),
                    //'placement' => 'right',
                ),
                'htmlOptions' => array(
                    'class' => 'numeric-column',
                ),
            ),
            array(
                //varchar(50)
                'class' => 'editable.EditableColumn',
                'name' => 'type',
                'editable' => array(
                    'url' => $this->createUrl('/d2files/d2filesType/editableSaver'),
                    //'placement' => 'right',
                )
            ),
            array(
                //varchar(50)
                'class' => 'editable.EditableColumn',
                'name' => 'model',
                'editable' => array(
                    'url' => $this->createUrl('/d2files/d2filesType/editableSaver'),
                    //'placement' => 'right',
                )
            ),

            array(
                'class' => 'TbButtonColumn',
                'buttons' => array(
                    'view' => array('visible' => 'Yii::app()->user->checkAccess("D2files.D2filesType.View")'),
                    'update' => array('visible' => 'FALSE'),
                    'delete' => array('visible' => 'Yii::app()->user->checkAccess("D2files.D2filesType.Delete")'),
                ),
                'viewButtonUrl' => 'Yii::app()->controller->createUrl("view", array("id" => $data->id))',
                'deleteButtonUrl' => 'Yii::app()->controller->createUrl("delete", array("id" => $data->id))',
                'deleteConfirmation'=>Yii::t('D2filesModule.crud_static','Do you want to delete this item?'),                    
                'viewButtonOptions'=>array('data-toggle'=>'tooltip'),   
                'deleteButtonOptions'=>array('data-toggle'=>'tooltip'),   
            ),
        )
    )
);
?>
<?php Yii::endProfile('D2filesType.view.grid'); ?>