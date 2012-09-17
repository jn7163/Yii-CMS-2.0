<?php
/**
 * Created by JetBrains PhpStorm.
 * User: artem.ostapetc
 * Date: 17.09.12
 * Time: 15:23
 * To change this template use File | Settings | File Templates.
 */
class FriendController extends ClientController
{
    public static function actionsTitles()
    {
        return array(
            'create' => t('Заявка в друзья'),
            'index'  => t('Просмотр друзей')
        );
    }


    public function sidebars()
    {
        return array(
            array(
                'actions'  => array(
                    'index',
                ),
                'sidebars' => array(
                    array(
                        'widget',
                        'application.modules.users.portlets.UserPageSidebar'
                    ),
                ),
            ),
            array(
                'actions' => array(
                    'index'
                ),
                'sidebars' => array(
                    array(
                        'widget',
                        'application.modules.users.portlets.UserFilterSidebar'
                    )
                )
            )
        );
    }


    public function subMenuItems()
    {
        $user_id = Yii::app()->user->id;

        return array(
            array(
                'label'   => t('Друзья'),
                'url'     => array('/social/friend/index', 'user_id' => $user_id),
                'visible' => Yii::app()->controller->action->id == 'index'
            ),
            array(
                'label'   => t('Входящие заявки'),
                'url'     => array('/social/friend/index', 'user_id' => $user_id, 'type' => 'in'),
                'visible' => Yii::app()->controller->action->id == 'index'
            ),
            array(
                'label'   => t('Исходящие заявки'),
                'url'     => array('/social/friend/index', 'user_id' => $user_id, 'type' => 'out'),
                'visible' => Yii::app()->controller->action->id == 'index'
            )
        );
    }


    public function actionCreate()
    {
        if (!isset($_POST['friend_id']) || Yii::app()->user->isGuest)
        {
            $this->badRequest();
        }

        $user = User::model()->throw404IfNull()->findByPk($_POST['friend_id']);

        $friend = new Friend();
        $friend->user_a_id = Yii::app()->user->id;
        $friend->user_b_id = $user->id;
        if ($friend->save())
        {
            if (isset($_POST['return']) && ($_POST['return'] == 'user_view_item'))
            {
                $this->renderPartial('application.modules.users.views.user._view', array(
                    'data' => $user
                ));
            }
        }
        else
        {
            echo CJSON::encode(array('errors' => $friend->errors_flat_array));
        }
    }


    public function actionIndex($user_id, $type = null)
    {
        $user = User::model()->throw404IfNull()->findByPk($user_id);

        switch ($type)
        {
            case null:
                $friends_ids = Friend::userFriendsIds($user->id);
                break;

            case 'in':
                $friends_ids = Friend::getIncomingFriendsIds($user_id);
                break;

            case 'out':
                $friends_ids = Friend::getOutcomingFriendsIds($user_id);
                break;
        }

        $criteria = new CDbCriteria();
        $criteria->addInCondition('id', $friends_ids);

        $data_provider = new CActiveDataProvider('User', array(
            'criteria' => $criteria
        ));

        $this->render('index', array(
            'data_provider' => $data_provider,
            'user'          => $user,
            'type'          => $type
        ));
    }
}
