<?php
/**
 * Created by PhpStorm.
 * User: Alexey Teterin
 * Email: 7018407@gmail.com
 * Date: 18.07.2015
 * Time: 18:36
 */
namespace Bolt\Extension\Errogaht\Mypobach;

use Bolt\Application;
use Bolt\BaseExtension;

class Extension extends BaseExtension
{


    public function initialize()
    {
        //$this->addCss('assets/extension.css');
        //$this->addJavascript('assets/start.js', true);
        $this->addTwigFunction('getScheduleArray', 'getScheduleArray');
        $this->addTwigFunction('getPhonesArray', 'getPhonesArray');
        $this->addTwigFunction('getImageSize', 'getImageSize');
    }

    public function getName()
    {
        return "Mypobach";
    }

    /**
     * Передаём методу строку с разделёнными через запятую телефонами
     * а он нам возвращает массив с телефонами
     * разбитыми по частям, для удобного использвания
     *
     * @param $phones
     *
     * @return array|bool
     */
    public function getPhonesArray($phones)
    {
        if (!empty($phones)) {
            $return = [];
            $phones = explode(',', $phones);
            foreach ($phones as $phone) {
                if (!empty($phone)) {
                    preg_match('/\+\s{0,}(\d+)\s{0,}\((\d+)\)\s{0,}(.+)/',
                        $phone, $m);
                    if (!empty($m[0]) && !empty($m[1]) && !empty($m[2]) && !empty($m[3])) {
                        $return[] = [
                            'fullPhone'      => "+ " . $m[1] . " (" . $m[2]
                                . ") "
                                . $m[3],
                            'withoutCountry' => "(" . $m[2] . ") " . $m[3],
                            'countryCode'    => $m[1],
                            'cityCode'       => $m[2],
                            'phone'          => $m[3],
                        ];
                    }
                }
            }

            return $return;

        }

        return false;
    }

    public function getImageSize($path)
    {

        $path = $this->app['paths']['rootpath'] . ltrim($path, "/");
        if (!is_readable($path) || !is_file($path)) {
            return false;
        }
        $imagesize = getimagesize($path);
        return ['width' => $imagesize[0], 'height' => $imagesize[1]];
    }
    /**
     * Передаём в функцию в Twig шаблоне массив с contenttype «schedule_records»
     * А функция возвращает структурированный удобно массив по дням неделей
     * С сортировкой внутри каждого дня недели по времени начала мероприятия
     */
    public function getScheduleArray($items)
    {
        /* тут мы получили от BOLT массив с элементами типа расписание */
        if (!empty($items)) {
            $return = [];

            foreach ($items as $item) {
                if (!empty($item->values['day'])) {
                    $dayName = $item->values['day'];
                    $dayNum = $this->getWeekdayNumber($dayName);
                    $return[$dayNum]['title'] = $dayName;
                    $return[$dayNum]['items'][] = $item;
                }
            }

            /**
             * в $return у нас сейчас довольно красивый многомерный массив с днями
             * неделями а в них со строками расписания на день
             */
            ksort($return);

            /**
             * Если у нас на этот день нет расписания - то просто делаем пустой день, а в шаблоне там
             * уже написано будет что или выходной или чо то ещё (если нету расписания на день)
             */
            foreach (range(1, 7) as $day) {
                if (empty($return[$day])) {
                    $return[$day]['title'] = $this->getWeekdayName($day);
                }
            }


            /* Сортируем каждый элемент внутри каждого дня так, что вначале у нас элементы
               у которых время начала раньше
               Если время начала одинаковое у двух программ, то будет выведена сначала та
               программа у которой время окончания больше, получается более длинная
            */
            foreach ($return as &$day) {
                if (!empty($day['items'])) {
                    uasort($day['items'], function ($a, $b) {
                        $timeFromA = $this->getTimeInSeconds($a->values['timefrom']);
                        $timeFromB = $this->getTimeInSeconds($b->values['timefrom']);
                        $timeToA = $this->getTimeInSeconds($a->values['timeto']);
                        $timeToB = $this->getTimeInSeconds($b->values['timeto']);

                        if ($timeFromA == $timeFromB) {
                            if ($timeToA == $timeToB) {
                                return 0;
                            } else {
                                return ($timeToA > $timeToB) ? -1 : 1;
                            }
                        }

                        return ($timeFromA < $timeFromB) ? -1 : 1;
                    });
                }
            }

            return $return;
        }

        return false;
    }

    /**
     * Возвращает порядковый номер дня недели
     *
     * @param $weekdayName
     *
     * @return bool
     */
    private function getWeekdayNumber($weekdayName)
    {
        $weekdayName = mb_strtolower($weekdayName);
        $weekdays = [
            'понедельник' => 1,
            'вторник'     => 2,
            'среда'       => 3,
            'четверг'     => 4,
            'пятница'     => 5,
            'суббота'     => 6,
            'воскресенье' => 7
        ];

        return isset($weekdays[$weekdayName]) ? $weekdays[$weekdayName] : '???';
    }

    /**
     * Возвращает название дня недели по его порядковому номеру
     *
     * @param $weekdayNum int
     *
     * @return bool
     */
    private function getWeekdayName($weekdayNum)
    {
        if (gettype($weekdayNum) != 'integer' && (int)$weekdayNum != $weekdayNum) {
            return false;
        }

        $weekdays = [
            1 => 'Понедельник',
            2 => 'Вторник',
            3 => 'Среда',
            4 => 'Четверг',
            5 => 'Пятница',
            6 => 'Суббота',
            7 => 'Воскресенье',
        ];

        return isset($weekdays[$weekdayNum]) ? $weekdays[$weekdayNum] : '???';
    }

    /**
     * Превращает время переданное в формате 09:59
     * в количество секунд, соответствующее часам и
     * минутам входящих в это время
     *
     * @param $time время в формате "09:30"
     *
     * @return bool
     */
    private function getTimeInSeconds($time)
    {
        if (!empty($time)) {
            $arr = date_parse($time);
            if ($arr) {
                $hours = $arr['hour'];
                $minutes = $arr['minute'];
                $seconds = $arr['second'];
                $totalSeconds = ($hours * 60 * 60) + ($minutes * 60) + $seconds;

                return $totalSeconds;
            }
        }

        return false;
    }
}






