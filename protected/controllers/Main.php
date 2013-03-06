<?php
namespace controllers;

use core\Controller;
use models\Users as UsersModel;
use core\Registry;

class Main extends Controller {

    const IN_OFFICE = 2;
    const OUT_OFFICE = 1;

    //Отрисовывает главную страницу, передаёт данные о посещениях пользователя по целому месяцу
    function indexAction() {
        $userInfo = Registry::getValue('user');
        $userId = $userInfo['id'];
        $usersModel = new UsersModel();
        $date = $this->getDate();
        $monthlyActions = $usersModel->getMonthlyUserActions($userId, strtotime($date));
        $monthlyPeriods = $this->formPeriods($monthlyActions);

        $weeklyActions = $usersModel->getWeeklyUserActions($userId, strtotime($date));
        $weeklyPeriods = $this->formPeriodsNew($weeklyActions);

        if (isset($weeklyPeriods['days'][$date])) {
            $currentDayPeriods = $weeklyPeriods['days'][$date];
        } else $currentDayPeriods = array();

        $this->render("Main/index.tpl", array(
            'date' => $date,
            'day' => $currentDayPeriods,
            'week' => $weeklyPeriods,
            'month' => $monthlyPeriods
            )
        );
    }

    //Возвращает дату либо из $_GET, либо текущую
    public function getDate() {
        if (!empty($_GET['date'])) {
            $unixtime = strtotime($_GET['date']);
            $date = date('Y-m-d', $unixtime);
        } else $date = date('Y-m-d');

        return $date;
    }

    //берёт все входы и выходы пользователя за определённый период и формирует из них периоды - пары входа и выхода,
    //с разницей по времени между ними
    private function formPeriods(array $actions) {
        $groupedActions = $this->groupActionsByDay($actions);
        $daysPeriods = array('total_sum' => 0, 'days' => array());

        foreach ($groupedActions as $day => $dayActions) {
            $daysPeriods['days'][$day] = array();
            $daysPeriods['days'][$day]['sum'] = 0;
            $daysPeriods['days'][$day]['periods'] = null;
            $acts = $dayActions['actions'];
            $actsCount = count($acts);
            $diff = 0;

            for ($i = 0; $i < $actsCount; $i++) {
                $enterTime = null;
                $exitTime = null;

                //нам нужно выделить пары вход + выход, иначе действия не влияют на общее время
                if ($acts[$i]['direction'] == self::IN_OFFICE
                    && isset($acts[$i+1])
                    && $acts[$i+1]['direction'] == self::OUT_OFFICE){

                    $enterTime = strtotime($acts[$i]['logtime']);
                    $exitTime = strtotime($acts[$i+1]['logtime']);
                    $diff = $exitTime - $enterTime;

                    $daysPeriods['days'][$day]['sum'] += $diff;
                    $daysPeriods['days'][$day]['periods'][] = array(
                        'enter' => $enterTime,
                        'exit' => $exitTime,
                        'diff' => $diff
                    );

                    $i++;
                } else {
                    $actionTime = strtotime($acts[$i]['logtime']);

                    if ($acts[$i]['direction'] == self::IN_OFFICE) {
                        $enterTime = $actionTime;
                    } else $exitTime = $actionTime;

                    $daysPeriods['days'][$day]['periods'][] = array(
                        'enter' => $enterTime,
                        'exit' => $exitTime,
                        'diff' => null
                    );
                }
            }

            $daysPeriods['total_sum'] += $daysPeriods['days'][$day]['sum'];
        }

        return $daysPeriods;
    }

    function formPeriodsNew(array $actions) {
        $daysPeriods = array();
        $daysPeriods['total_sum'] = 0;

        $actsCount = count($actions);
        for ($i = 0; $i < $actsCount; $i++) {
            $diff = 0;
            $enterTime = null;
            $exitTime = null;
            $day = $actions[$i]['day'];
            if (!isset($daysPeriods['days'][$day])) $daysPeriods['days'][$day] = array();
            if (!isset($daysPeriods['days'][$day]['sum'])) $daysPeriods['days'][$day]['sum'] = 0;
            if (!isset($daysPeriods['days'][$day]['periods'])) $daysPeriods['days'][$day]['periods'] = array();

            //нам нужно выделить пары вход + выход, иначе действия не влияют на общее время
            if ($actions[$i]['direction'] == self::IN_OFFICE
                && isset($actions[$i+1])
                && $actions[$i+1]['direction'] == self::OUT_OFFICE){

                $enterTime = strtotime($actions[$i]['logtime']);
                $exitTime = strtotime($actions[$i+1]['logtime']);
                $diff = $exitTime - $enterTime;

                $daysPeriods['days'][$day]['sum'] += $diff;
                $daysPeriods['days'][$day]['periods'][] = array(
                    'enter' => $enterTime,
                    'exit' => $exitTime,
                    'diff' => $diff
                );

                $i++;
            } else {
                $actionTime = strtotime($actions[$i]['logtime']);

                if ($actions[$i]['direction'] == self::IN_OFFICE) {
                    $enterTime = $actionTime;
                } else $exitTime = $actionTime;

                $daysPeriods['days'][$day]['periods'][] = array(
                    'enter' => $enterTime,
                    'exit' => $exitTime,
                    'diff' => null
                );

            }

            $daysPeriods['total_sum'] += $diff;
        }

        return $daysPeriods;
    }

    private function groupActionsByDay($actions) {
        $groupedActions = array();

        foreach ($actions as $action) {
            $day = date('d-m-Y', strtotime($action['logtime']));
            $groupedActions[$day]['actions'][] = $action;
        }

        return $groupedActions;
    }

}