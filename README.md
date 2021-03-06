# d2files (fork from Dbrisinajumi/d2files)


## Features

* attach files to model record
* widget for model view

## Installation
 * install https://github.com/blueimp/jQuery-File-Upload?source=c
 * install https://github.com/ivarsju/d2files.git
```bash
php composer.phar require ivarsju/d2files dev-master
```

 * add to config/main.php
```php
    'aliases' => array(
        'd2files' => 'vendor.ivarsju.d2files',
    ),
    'import' => array(
        'd2files.models.*',
        'd2files.widgets.*', // shared classes
    ),
    'modules' => array(
        'd2files' => array(
            'class' => 'd2files.D2filesModule',
            'upload_dir' => 'root.upload',
            'accept_file_types' => '/\.(gif|pdf|dat|jpe?g|png|doc|docx|xls|xlsx|htm)$/i',
            
            //automaticly registre tasks, when upload files to model 
            // d2person.PprsPerson, if user role is Agent or Client
            'registre_tasks_to_models' => array(
                'd2person.PprsPerson' => array(
                    'new_project_status' => 1, //Not started
                    'task_init_status' => 1, //Active
                    'task_due_in_days' => 3,
                    'user_roles' => array('Agent','Client'),
                ),
            ),
            'shareable_by_link' => [
                'my_module.my_model' => [
                    'allow_ip' => [
                        '127.0.0.1',
                    ],
                    'salt' => '1212133243243',
                ]
            ]
        ),  
    ),
```
* add to config/console.php
```
    'aliases' => array(
        'd2files' => 'vendor.ivarsju.d2files',
    ),
    'commandMap' => array(
        'migrate' => array(
            'modulePaths' => array(
                'd2files' => 'd2files.migrations',
            )
        )
    )
```

* execute yiic migration
* in table d2files can define file types for model.

## Usage
### VIEW
```php
    $this->widget(
        'd2FilesWidget',
        [
            'module'    => $this->module->id, 
            'model'     => $model,
            'title'     => 'Wiget Title',
            'icon'      => false,
            'hideTitle' => false,
            'readOnly'  => false,
        ]
    );
```

### Requirements
* To upload file user must have "ModuleName.ModelName.uploadD2File" access rights for caller module
* To delete file user must have "ModuleName.ModelName.deleteD2File" access rights for caller module
* To download, upload or delete file user must have read access to corresponding item it is attached to 
 and "ModuleName.ModelName.downloadD2File"

### sql statement example for giving access

    INSERT INTO `authitem` 
    (`name`, `type`, `description`, `bizrule`, `data`) 
    VALUES
    ('D2company.CcmpCompany.uploadD2File','0','D2company.CcmpCompany upolad D2Files',NULL,'N;'),
    ('D2company.CcmpCompany.downloadD2File','0','D2company.CcmpCompany downloas D2Files',NULL,'N;'),
    ('D2company.CcmpCompany.deleteD2File','0','D2company.CcmpCompany delete D2Files',NULL,'N;')
    ;

    INSERT INTO `authitemchild` (`parent`, `child`) VALUES ('Agent', 'D2company.CcmpCompany.uploadD2File'); 
    INSERT INTO `authitemchild` (`parent`, `child`) VALUES ('Agent', 'D2company.CcmpCompany.downloadD2File'); 
    INSERT INTO `authitemchild` (`parent`, `child`) VALUES ('Agent', 'D2company.CcmpCompany.deleteD2File'); 

