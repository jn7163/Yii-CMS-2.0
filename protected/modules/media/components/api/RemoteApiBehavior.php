<?php
Yii::import('media.components.api.ApiBehaviorAbstract');
class RemoteApiBehavior extends ApiBehaviorAbstract {

    public $new_record_status;


    public function parse($content)
    {
        return false;
    }


    public function getThumb($size = null, $crop = true)
    {
        if (is_string($size)) {
            $size = [
                'width'  => 64,
                'height' => 64
            ];
        }

        $dir = pathinfo($this->getPk(), PATHINFO_DIRNAME);
        $name = pathinfo($this->getPk(), PATHINFO_BASENAME);
        return ImageHelper::thumb($dir, $name, $size, $crop)->getSrc();
    }


    public function getHref()
    {
        return $this->pk;
    }


    public function getType()
    {
        $doc = [
            'book',
            'archive',
            'word',
            'excel'
        ];
        switch (true) {
            case $this->typeIs($doc):
                return MediaFile::TYPE_DOC;
            case $this->typeIs('audio'):
                return MediaFile::TYPE_AUDIO;
            case $this->typeIs('video'):
                return MediaFile::TYPE_VIDEO;
            case $this->typeIs('image'):
                return MediaFile::TYPE_IMG;
        }
    }


    protected function typeIs($types)
    {
        foreach ((array)$types as $type) {
            if (!in_array($this->extension, FileType::${$type . 'Extensions'})) {
                return false;
            }
        }
        return true;
    }

    public function getPlayer($size = null)
    {
        $owner = $this->getOwner();
        switch (true)
        {
            case $owner->type == MediaFile::TYPE_VIDEO:
                return Yii::app()->controller->widget('media.portlets.VideoPlayer', [
                    'src' => $this->getHref(),
                    'title' => $owner->title,
                    'size'  => $size,
                    'poster'  => $owner->preview
                ], true);
            default:
                return '';
        }

    }

    public function getPreviewArray($size_name = null)
    {
        $folder = Yii::app()->getModule('media')->assetsUrl() . '/img/icons/';
        switch (true) {
            case $this->typeIs('image'):
                return [
                    'type' => 'img',
                    'val'  => $this->getThumb()
                ];
                break;
            case $this->typeIs('audio'):
                $name = 'audio';
                break;
            case $this->typeIs('excel'):
                $name = 'excel';
                break;
            case $this->typeIs('word'):
                $name = 'word';
                break;
            case $this->typeIs('archive'):
                $name = 'rar';
                break;
            case $this->typeIs('video'):
                return [
                    'type' => 'video',
                    'val'  => $this->pk
                ];
                break;
            default:
                if (is_file('.' . $folder . $this->extension . '.jpg')) {
                    $name = $this->extension;
                } else {
                    $name = 'any';
                }
                break;
        }

        return [
            'type' => 'img',
            'val'  => $folder . $name . '.jpg'
        ];
    }


    public function getPreview($size = null)
    {
        $data = $this->getPreviewArray($size);

        switch ($data['type']) {
            case 'img':
                return CHtml::image($data['val']);
                break;
            case 'video':
                return CHtml::tag('div', [
//                    "data-video" => $data['val'],
                    "data-width"  => 64,
                    "data-height" => 64
                ], true, true);
                break;
        }
    }


    public function getExtension()
    {
        return pathinfo($this->getPk(), PATHINFO_EXTENSION);
    }


    public function beforeSave($event)
    {
        $owner = $this->getOwner();
        if ($owner->getIsNewRecord()) {
            $this->setPk($this->pk);
            $owner->type = $this->getType();
            $owner->status = $this->new_record_status;
            return true;
        }
        return true;
    }

}