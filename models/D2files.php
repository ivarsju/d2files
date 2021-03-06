<?php

// auto-loading
Yii::setPathOfAlias('D2files', dirname(__FILE__));
Yii::import('D2files.*');

class D2files extends BaseD2files
{

    /**
     * shareable definition for model
     * @var type 
     */
    public $shareable_def = false;
    
    // Add your model-specific methods here. This file will not be overriden by gtc except you force it.
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function behaviors() {
        return array_merge(
                parent::behaviors(), array(
             //auditrail       
            'LoggableBehavior' => array(
                'class' => 'LoggableBehavior'
            ),
        ));
    }

    public function search($criteria = null)
    {
        if (is_null($criteria)) {
            $criteria = new CDbCriteria;
        }
        return new CActiveDataProvider(get_class($this), array(
            'criteria' => $this->searchCriteria($criteria),
        ));
    }
    
    public function afterSave() {
        
        /**
         * registre file in task
         */
        $registre_tasks_to_models = Yii::app()->getModule('d2files')->registre_tasks_to_models;
        if($registre_tasks_to_models 
                && isset($registre_tasks_to_models[$this->model])
        ){
            $this->createProject($registre_tasks_to_models[$this->model]);
            
        
            
        }
        parent::afterSave();
    }
    
    public function searchExactCriteria($criteria = null)
    {
        if (is_null($criteria)) {
            $criteria = new CDbCriteria;
        }

        $criteria->compare('t.id', $this->id);
        $criteria->compare('t.type_id', $this->type_id);
        $criteria->compare('t.file_name', $this->file_name);
        $criteria->compare('t.upload_path', $this->upload_path);
        $criteria->compare('t.add_datetime', $this->add_datetime);
        $criteria->compare('t.user_id', $this->user_id);
        $criteria->compare('t.deleted', $this->deleted);
        $criteria->compare('t.notes', $this->notes);
        $criteria->compare('t.model', $this->model);
        $criteria->compare('t.model_id', $this->model_id);


        return $criteria;

    }
    
    /**
     * create project 
     * @param array $settings 
     * @return boolean
     */
    public function createProject($settings){
    
        //currently only for persons create project
        if($this->model != 'd2person.PprsPerson'){
            return false;
        }

        //validate user roles with setting roles
        if(isset($settings['user_roles'])){
            $user_roles = Authassignment::model()->getUserRoles(Yii::app()->user->id);
            $a = array_intersect($user_roles,$settings['user_roles']);
            if(empty($a)){
                return false;
            }
        }        
        
        $model = PprsPerson::model()->findByPk($this->model_id);
        
        //create project
        $ttsk = new TtskTask;
        $ttsk->ttsk_pprs_id = $this->model_id;
        $ttsk->ttsk_name = 'New attachment to ' . $model->itemLabel;
        $ttsk->ttsk_description = '';
        $ttsk->ttsk_tstt_id = $settings['new_project_status']; //not started
        try {
            if (!$ttsk->save()) {
                return false;
            }
        } catch (Exception $e) {
            return false;            
        }        
        
        //create task
        $tcmn = new TcmnCommunication;
        $tcmn->tcmn_ttsk_id = $ttsk->ttsk_id;
        $tcmn->tcmn_task  = 'Validate attachment:' . PHP_EOL;
        $tcmn->tcmn_task .= $this->file_name . ' ' . $this->add_datetime;
        $tcmn->tcmn_tcst_id = $settings['task_init_status'];
        $tcmn->tcmn_datetime = new CDbExpression('ADDDATE(NOW(),'.$settings['task_due_in_days'].' )');
        try {
            if (!$tcmn->save()) {
                return false;
            }
        } catch (Exception $e) {
            return false;            
        }                    
        
        return true;
    }    
    
    public static function extendedCheckAccess($authitem,$exception_on = true){
        $sql = "select * from AuthItem where `name` = '" .$authitem. "'";
        $ai = Yii::app()->db->createCommand($sql)->queryAll();         
        
        //if auth item is defined, use strict validation
        if(empty($ai)){
            $a = explode('.',$authitem);
            switch ($a[2]) {
                case 'uploadD2File':
                    $a[2] = 'Create';    
                    break;

                case 'downloadD2File':
                    $a[2] = 'View';    
                    break;
                case 'deleteD2File':
                    $a[2] = 'Delete';    
                    break;
            }            
            $authitem = implode('.',$a);
        }
        
        if (!Yii::app()->user->checkAccess($authitem)) {
            if($exception_on){
                throw new CHttpException(403, Yii::t("D2filesModule.model","You are not authorized to perform this action."));
            }
            return false;
        }            
        return true;

    }
    
    /**
     * get first file full path for model record with requested type
     * @param string $model_name model name in format [module_name].[model_name]
     * @param int $model_id model record id
     * @param int $type file type
     * @return string/boolean
     */
    public static function getFileFullPathByType($model_name,$model_id,$type){
        
        /**
         * get record
         */
        $criteria = new CDbCriteria;
        $criteria->compare('model',$model_name);
        $criteria->compare('model_id',$model_id);
        $criteria->compare('type_id',$type);
        $criteria->compare('deleted',0);

        $d2files = D2files::model()->find($criteria);
        if(!$d2files){
            return false;
        }
        
        /**
         * get path and saved file name
         */
        Yii::import("d2files.compnents.*");
        $dir_path = UploadHandlerD2files::getUploadDirPath($model_name);
        $file_name = UploadHandlerD2files::createSaveFileName($d2files->id, $d2files->file_name);
        
        /**
         * return full path
         */
        return $dir_path . $file_name;
        
        
    }    
    
    public function getFileFullPath(){
        Yii::import("d2files.compnents.*");
        $dir_path = UploadHandlerD2files::getUploadDirPath($this->model);
        $file_name = UploadHandlerD2files::createSaveFileName($this->id, $this->file_name);        
        
        /**
         * return full path
         */
        return $dir_path . $file_name;        
    }

        /**
     * get model record files
     * @param string $model_name model name in format [module name].[model name]
     * @param int $model_id record is
     * @param int $type attachment type
     * @return array d2files models
     */
    public static function getModelRecorFiles($model_name,$model_id,$type = false){
        
        $criteria = new CDbCriteria;
        $criteria->compare('model',$model_name);
        $criteria->compare('model_id',$model_id);
        if($type){
            $criteria->compare('type_id',$type);
        }
        $criteria->compare('deleted',0);

        return D2files::model()->findAll($criteria);
    }    
    
    /**
     * get shareable configuration for actual model
     * @return array
     */    
    public function getShareAbleDef(){
        if(!$this->shareable_def){
            $shareable = Yii::app()->getModule('d2files')->shareable_by_link;
            foreach ($shareable as $sh_model => $def){
                if($sh_model == $this->model){
                    $this->shareable_def = $def;
                    break;
                }
            }
            
        }
        return $this->shareable_def;
    }


    /**
     * generate hash for shareable file
     * @return boolean
     */
    public function genHashForShareAbleFile(){

        /**
         * get shareable file configuration for actual model
         */
        $def = $this->getShareAbleDef();
        
        /**
         * shareable file must be defined in d2files config
         */
        if(!$def){
            return false;
        }

        /**
         * get salt
         */
        $salt = 'd2filessalt';
        if(isset($def['salt'])){
            $salt = 'd2filessalt';            
        }
        
        /**
         * create hash with salt
         */        
        return hash('sha256',$this->file_name.$this->add_datetime.$this->model_id,$def['salt']);
    }
    
    /**
     * create url for shareable file (must be defined in d2files configuration
     * Example: http://depo2.yii/index.php?r=d2files/d2files/downloadShareAbleFile&id=4&h=%D9%C7%C0%2CH%D8%AF%E6%3E%DE%E6%85%1D%E9%EAn%E97%7D%F9%C1BLe%02%A4%E2%C6%5B%40%F4%CD
     * @return string/boolean 
     */
    public function getShareAbleLink(){
        
        $h = $this->genHashForShareAbleFile();
        
        /**
         * shareable file must be defined in d2files config
         */        
        if(!$h){
            return false;
        }
        
        /**
         * create full link
         */
        return '/index.php?'
            .'r=d2files/d2files/downloadShareAbleFile'
            .'&id='.$this->id
            .'&h=' . urlencode($h);
    }
}
