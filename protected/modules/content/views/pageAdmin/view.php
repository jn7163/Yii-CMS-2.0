<?php
$this->page_title = $model->title;

$this->widget('DetailView', array(
	'data'=>$model,
	'attributes'=>array(
        'title',
		array('name' => 'lang', 'value' => $model->language->name),
		'url',
		array(
            'name'  => 'is_published',
            'value' => $model->is_published ? "да" : "нет"
        ),
		'date_create',
        array(
            'name'  => 'Мета-теги',
            'value' => MetaTag::model()->html($model->id, get_class($model)),
            'type'  => 'raw'
        ),
		array(
            'name'  => 'text',
            'type'  => 'raw',
            'value' => $model->text
        ),
	),
));
?>
