<?php

/**
 *  API 2
 * @see https://tech.yandex.ru/metrika/doc/api2/concept/about-docpage/
 */
class shopMetrikaPluginBackendGetController extends waJsonController
{
    // Максимальное количество записей на странице
    const PER_PAGE = 730;
    const CLIENT_ID = 'e9212e398adb4e1984db0bff3ec4669c';

    public function execute()
    {
        $date_start = $this->dateConvert(waRequest::request('date_start'));
        $date_end = $this->dateConvert(waRequest::request('date_end'));
        $app_settings_model = new waAppSettingsModel();
        $settings = $app_settings_model->get(array('shop', 'metrika'));
        if (empty($settings['count'])) {
            $error = '<p>Укажите номер счетчика в настройках плагина.</p>';
            $this->setError($error);
        } elseif (empty($settings['token'])) {
            $error = '<p>1. Для настройки плагина перейдите по ссылке на сайт <a href="https://oauth.yandex.ru/authorize?response_type=code&client_id=' . self::CLIENT_ID . '" target="_blank">Яндекса</a> под аккаунтом, на котором размещен ваш счетчик.</p><from action="" method="post">2. Введите код подтверждения полученный в п.1: <input type="text" name="code"> <input type="submit" value="Сохранить" class="button green send-code">';
            $this->setError($error);
        } else {
            $this->getData($settings['token'], $settings['count'], $date_start, $date_end);
        }
    }

    /**
     * @param $token
     * @param $count
     * @param $date_start
     * @param $date_end
     */
    private function getData($token, $count, $date_start, $date_end)
    {
        $type = waRequest::get('type', waRequest::TYPE_STRING_TRIM);
        $url = $this->getUrl($type) . '&id=' . $count . '&date1=' . $date_start . '&date2=' . $date_end . '&limit=' . self::PER_PAGE;

        try {

            $options = array(
                'authorization' => false
            );
            $header = array(
                'Authorization' => 'OAuth ' . $token
            );

            $net = new waNet($options, $header);

            $net->query("https://api-metrika.yandex.ru/stat/v1/data" . $url);
            $metrika_o = $net->getResponse();


            $out = $this->getStat(json_decode($metrika_o), $type);

            if ($out) {
                $this->response = $out;
            } else {
                $this->setError('Нет данных для отображения');
            }

        } catch (Exception $ex) {
            $result = $ex->getMessage();
            $this->setError(json_decode($result)->message);
        }

    }

    /**
     * @param $metrika_o
     * @param $type string
     * @return array
     */
    private function getStat($metrika_o, $type)
    {

        $out = array();
        switch ($type) {
            case "traffic":
                /* Сводная статистика */
                $out['totals_visits'] = 0;
                $out['totals_denial'] = 0;
                $out['totals_page_views'] = 0;
                $out['totals_new_visitors'] = 0;
                $out['totals_depth'] = 0;

                foreach ($metrika_o->data as $v => $a) {
                    $out['data'][$v]['name'] = $this->dateConvertOut($a->dimensions[0]->name);
                    $out['data'][$v]['visits'] = $a->metrics[0];
                    $out['data'][$v]['denial'] = round($a->metrics[4], 1) . "%";
                    $out['data'][$v]['page_views'] = $a->metrics[2];
                    $out['data'][$v]['new_visitors'] = round($a->metrics[3], 1) . "%";
                    $out['data'][$v]['new_visitors_count'] = round(($a->metrics[0] * $a->metrics[3]) / 100, 0);
                    $out['data'][$v]['depth'] = round($a->metrics[5], 1);

                    /** Суммарные данные */
                    $out['totals_visits'] = $out['totals_visits'] + $a->metrics[0];
                    $out['totals_denial'] = $out['totals_denial'] + $a->metrics[4];
                    $out['totals_page_views'] = $out['totals_page_views'] + $a->metrics[2];
                    $out['totals_new_visitors'] = $out['totals_new_visitors'] + $a->metrics[3];
                    $out['totals_depth'] = $out['totals_depth'] + $a->metrics[5];
                }

                $out['totals_denial'] = round($out['totals_denial'] / count($metrika_o->data), 1) . "%";
                $out['totals_new_visitors'] = round($out['totals_new_visitors'] / count($metrika_o->data), 2) . "%";
                $out['totals_depth'] = round($out['totals_depth'] / count($metrika_o->data), 1);
                $out['totals_new_visitors_count'] = round(($out['totals_visits'] * $out['totals_new_visitors']) / 100, 0);

                $this->getStorage()->write('totals_visits', $out['totals_visits']);
                break;
            case "sources":
                /* Источники */
                foreach ($metrika_o->data as $v => $a) {
                    $out[$v]['name'] = $a->dimensions[0]->name;
                    $out[$v]['id'] = $a->dimensions[0]->id;
                    $out[$v]['visits'] = $a->metrics[0];
                    $out[$v]['users'] = $a->metrics[1];
                    $out[$v]['bounceRate'] = round($a->metrics[2], 1) . "%";
                    $out[$v]['pageDepth'] = round($a->metrics[3], 1);
                    $out[$v]['avgVisitDurationSeconds'] = round($a->metrics[4], 0);
                }
                break;
            /* Источники подбробнее */
            case "ad":
            case "organic":
            case "referral":
            case "social":
                foreach ($metrika_o->data as $v => $a) {
                    $out[$v]['name'] = $a->dimensions[0]->name;
                    $out[$v]['visits'] = $a->metrics[0];
                    $out[$v]['users'] = $a->metrics[1];
                    $out[$v]['bounceRate'] = round($a->metrics[2], 1) . "%";
                    $out[$v]['pageDepth'] = round($a->metrics[3], 1);
                    $out[$v]['avgVisitDurationSeconds'] = round($a->metrics[4], 0);
                }
                break;
            /* Поисковые фразы*/
            case "phrases":
                foreach ($metrika_o->data as $v => $a) {
                    $out[$v]['name'] = $a->dimensions[0]->name;
                    $out[$v]['url'] = $a->dimensions[0]->url;
                    $out[$v]['favicon'] = $a->dimensions[0]->favicon;
                    $out[$v]['icon_id'] = $a->dimensions[1]->id;
                    $out[$v]['visits'] = $a->metrics[0];
                    $out[$v]['users'] = $a->metrics[1];
                    $out[$v]['bounceRate'] = round($a->metrics[2], 1) . "%";
                    $out[$v]['pageDepth'] = round($a->metrics[3], 1);
                    $out[$v]['avgVisitDurationSeconds'] = round($a->metrics[4], 0);
                }
                break;
            case "popular":
                /* Популярные страницы */
                foreach ($metrika_o->data as $v => $a) {
                    $out[$v]['name'] = $a->dimensions[0]->name;
                    $out[$v]['pageViews'] = $a->metrics[0];
                    $out[$v]['users'] = $a->metrics[1];
                    $out[$v]['pageDepth'] = round($a->metrics[0] / $a->metrics[1], 1);
                }

                break;
            /* Конверсия */
            case "conversion":
                foreach ($metrika_o->data as $v => $a) {
                    $out[$v]['name'] = $a->dimensions[0]->name;
                    $out[$v]['visits'] = $a->metrics[0];
                    $out[$v]['users'] = $a->metrics[1];
                    $out[$v]['pageViews'] = $a->metrics[2];
                    $out[$v]['conversion'] = round(($a->metrics[0] * 100) / $this->getStorage()->read('totals_visits'), 1) . "%";;
                }
                break;
            /* Разрешение экрана */
            case "tech_display":
                foreach ($metrika_o->data as $v => $a) {
                    $out[$v]['name'] = $a->dimensions[0]->name . "x" . $a->dimensions[1]->name;
                    $out[$v]['visits'] = $a->metrics[0];
                    $out[$v]['users'] = $a->metrics[1];
                    $out[$v]['bounceRate'] = round($a->metrics[2], 1) . "%";
                    $out[$v]['pageDepth'] = round($a->metrics[3], 1);
                    $out[$v]['avgVisitDurationSeconds'] = round($a->metrics[4], 0);
                }
                break;
            /* Поисковые фразы, География, Демография, Система, Возраст, Пол, Браузер, Мобильность, Устройства */
            case "geo":
            case "demography":
            case "age_gender":
            case "os":
            case "browsers":
            case "mobile":
            case "devices":
            case "interests":
            default:
                foreach ($metrika_o->data as $v => $a) {
                    $out[$v]['name'] = $a->dimensions[0]->name;
                    $out[$v]['visits'] = $a->metrics[0];
                    $out[$v]['users'] = $a->metrics[1];
                    $out[$v]['bounceRate'] = round($a->metrics[2], 1) . "%";
                    $out[$v]['pageDepth'] = round($a->metrics[3], 1);
                    $out[$v]['avgVisitDurationSeconds'] = round($a->metrics[4], 0);
                }
                break;
        }

        return $out;

    }

    /**
     * Формирования URL
     * @param $action string
     * @return string
     */
    private function getUrl($action)
    {
        switch ($action) {
            case "traffic":
                $url = '?preset=traffic&group=day';
                break;
            case "sources":
                $url = '?preset=sources_summary&dimensions=ym:s:lastTrafficSource';
                break;
            case "ad":
                $url = '?preset=adv_engine';
                break;
            case "referral":
                $url = '?preset=sources_sites&dimensions=ym:s:externalRefererHash';
                break;
            case "organic":
                $url = '?preset=search_engines&dimensions=ym:s:lastSearchEngine';
                break;
            case "social":
                $url = '?preset=sources_social';
                break;
            case "popular":
                $url = '?preset=popular&dimensions=ym:pv:URLHash';
                break;
            case "phrases":
                $url = '?preset=sources_search_phrases';
                break;
            case "interests":
                $url = '?preset=interests&metrics=ym:s:visits,ym:s:users,ym:s:bounceRate,ym:s:pageDepth,ym:s:avgVisitDurationSeconds';
                break;
            case "conversion":
                $url = '?preset=conversion&group=week';
                break;
            case "geo":
                $url = '?preset=geo_country&dimensions=ym:s:regionCountry';
                break;
            case "demography":
                $url = '?preset=gender';
                break;
            case "age_gender":
                $url = '?preset=age';
                break;
            case "os":
                $url = '?preset=tech_platforms&dimensions=ym:s:operatingSystemRoot';
                break;
            case "browsers":
                $url = '?preset=tech_browsers&dimensions=ym:s:browser';
                break;
            case "tech_display":
                $url = '?preset=tech_display&group=day';
                break;
            case "mobile":
                $url = '?preset=tech_devices&dimensions=ym:s:mobilePhoneModel';
                break;
            case "devices":
                $url = '?preset=tech_devices&dimensions=ym:s:deviceCategory';
                break;
            default:
                $url = '?preset=traffic&group=day';
        }

        return $url;
    }

    /**
     * Конвертация даты для передачи в Метрику
     * @param $date string
     * @return string
     */
    private function dateConvert($date)
    {
        $date = explode('.', $date);
        return $date[2] . $date[1] . $date[0];
    }

    /**
     * Обратная конвертация даты для отображения в отчетах
     * @param $date string
     * @return string
     */
    private function dateConvertOut($date)
    {
        return date("d.m.Y", strtotime($date));
    }
}