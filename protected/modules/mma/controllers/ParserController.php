<?php
/**
 * Created by JetBrains PhpStorm.
 * User: os
 * Date: 09.10.12
 * Time: 21:57
 * To change this template use File | Settings | File Templates.
 */

Yii::import('application.modules.mma.components.*');

class ParserController extends ClientController
{
    public static function actionsTitles()
    {
        return array(
            'parsePost' => 'Парсинг поста',
            'parse'     => 'Парсинг'
        );
    }


    public function actionParsePost()
    {
        if (isset($_POST['url']) && isset($_POST['parser']))
        {
            $parser = new $_POST['parser'];

            if ($_POST['parser'] == 'MixfightParser')
            {
                $post = $parser->parseAndSavePost($_POST['url'], null, '');
            }
            else
            {
                $post = $parser->parsePost($_POST['url']);
            }

            p($post);
        }

        $this->render('parsePost'   );
    }


    public function actionParse($command)
    {
        $cr = new CConsoleCommandRunner();
        $cr->addCommands(APP_PATH . 'commands');
        p($cr->run(array('yiic', $command, 'parsePosts')));
    }

}
