<?php

namespace Bolt\Extension\ZolotoyWeb\Sindy;

use Bolt\Application;
use Bolt\BaseExtension;
use Bolt\Content;

class Extension extends BaseExtension
{
    public $dfdasfsdf = "lol";
    private $sdfsdfsd = 'opsss';
    public static $zopa = 'dsfsdf';

    public function initialize()
    {
        $this->addTwigFunction('breadcrumb', 'breadcrumb');
    }

    public function getName()
    {
        return "Sindy";
    }

    /**
     * Возвращает всех родителей для хлебных крошек
     */
    public function breadcrumb()
    {
        /** @var array массив цепочки хлебных крошек */
        $parents = [];
        $record = $this->app['twig']->getGlobals()['record'];
        $parents[] = $record;

        /** @var mixed родитель данной записи */
        while ($record = $this->getParentRecord($record)) {
            array_unshift($parents, $record);
        }
        return $parents;
    }

    /**
     * Возвращает record родителя
     * @param $record
     *
     * @return bool
     */
    private function getParentRecord($record)
    {
        if (!empty($record)) {
            $fieldInfo = $record->fieldinfo('parent');
            $fieldType = $record->fieldtype('parent');
            if ($fieldInfo && $fieldType === 'select' && !empty($fieldInfo['values'])) {
                if (preg_match('/^(\w+)\/(\w+)$/', $fieldInfo['values'], $matches) && !empty($matches[1])) {
                    $parentContentType = $matches[1];
                    $parentId = $record->get('parent');
                    if (!empty($parentId)) {
                        $parentRecord = $this->app['storage']->getContent($parentContentType . '/' . $parentId);
                        return $parentRecord;
                    }
                }
            }

            return false;
        }
    }
}
