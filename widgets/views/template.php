<?php

$sFileListHtml = '<table id="attachment_list_'.$this->getId().'" class="items table table-condensed table-bordered">';
if (D2files::extendedCheckAccess($model . '.downloadD2File', false)) {
    
    $colspan = 3;
    if(empty($files_types_list)){
        $colspan = 2;
    }
    //file list
    foreach ($files as $mfile) {
        
        //editable file type
        $file_type = '';
        if(!empty($files_types_list)){
            $file_type = '<td class="file-type">'
                    . $this->widget(
                        'EditableField',
                        array(
                            'model' => $mfile,
                            'type' => 'select',
                            'attribute' => 'type_id',
                            'url' => Yii::app()->controller->createUrl('/d2files/d2files/editableSaver'),
                            'source' => $files_types_list,
                            'placement' => 'left',
                            'apply' =>  $readOnly,
                        ),
                        true
                    )
                    . '</td>';
        }
        
        $file_delete_ajax_url = '';
        if (!$readOnly && D2files::extendedCheckAccess($model . '.deleteD2File', false)) {
            $delete_url = $this->controler->createUrl('deleteFile', array('id' => $mfile->id),'&amp;');
            $file_delete_ajax_url = '<a href="' . $delete_url . '" rel="tooltip" title="' . Yii::t("D2filesModule.crud_static", "Delete") . '" class="delete" data-toggle="tooltip"><i class="icon-trash"></i></a> ';
        }

        $file_download_ajax_url = $this->controler->createUrl('downloadFile', array('id' => $mfile->id),'&amp;');
        
        $short_name = mb_strlen($mfile->file_name, 'utf8') > 30 ? mb_substr($mfile->file_name, 0, 27) . '...' : $mfile->file_name;
        $file_title = Yii::t("D2filesModule.crud_static", "Download") . ': ' . $mfile->file_name;
        
        $sFileListHtml .= '<tr id="d2file-' . $mfile->id . '">'
            . '<td><i class="icon-file-text blue"></i> '
            . '<a href="' . $file_download_ajax_url . '" rel="tooltip" title="' . $file_title . '" class="download" data-toggle="tooltip">' . $short_name . '</a></td>'
            . $file_type
            . '<td class="button-column">'
            . '<a href="' . $file_download_ajax_url . '" rel="tooltip" title="' . $file_title . '" class="download" data-toggle="tooltip"><i class="icon-download-alt"></i></a> '
            . $file_delete_ajax_url
            . '</td>'
            . '</tr>';
        
        if (D2files::extendedCheckAccess($model . '.uploadD2File', false)) {
        
            $sFileListHtml .= '<tr id="d2cmnt-' . $mfile->id . '"><td colspan="'.$colspan.'">';
            
            $sFileListHtml .= $this->widget(
                'EditableField',
                array(
                    'model' => $mfile,
                    'attribute' => 'notes',
                    'url' => Yii::app()->controller->createUrl('/d2files/d2files/editableSaver'),
                    'emptytext' => Yii::t("D2filesModule.crud_static", "Add comment"),
                    'placement' => 'right',
                    'apply' =>  !$readOnly,
                ),
                true
            );

            $sFileListHtml .= '</td></tr>';
        
        }
        
    }
}
$sFileListHtml .= '</table>';

$file_form = '<form method="post" id="d2FileUploadForm_'.$this->getId().'" name="DataForm" enctype="multipart/form-data">' .
        '<input id="fileupload_'.$this->getId().'" type="file" name="files[]"  style="display: none;" multiple />
                                ' . $sFileListHtml . '
                                </form>';

if (!$readOnly && D2files::extendedCheckAccess($model . '.uploadD2File', false)) {
    echo "<tr id=\"dropZone_".$this->getId()."\" class=\"dropZone\" style=\"border: 3px dashed #ccc;\"><td style=\"vertical-align: middle; padding-left:10px;\"><span class=\"bigger-110 bolder\" style=\"vertical-align: middle; line-height: 43px;\"><i class=\"icon-cloud-upload grey\"></i> {label}</span> {value}</td></tr>\n";
}
echo "<tr><td class=\"no-padding-left no-padding-right\">" . $file_form . "</td></tr>\n";
