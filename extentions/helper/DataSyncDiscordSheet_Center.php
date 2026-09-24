<?php


class DataSyncDiscordSheet_Center
{
    public function getDataSyncDiscordSheet($formName, $referrer, $record)
    {
        try {
            $option_name = 'vnx_sync_telegram_sheet_setting';
            $dataOption = get_option($option_name);

            $vnx_sync_telegram_sheet_setting = $dataOption !== false ? json_decode($dataOption, true) : [];

            // Mang TRA VE cho ben goi (api_send_message_bot.php). Khac voi mang LUU vao
            // option ben duoi (dung bo key ma view va handler luu form dang dung).
            $result = [
                'dataSendDiscord' => "",
                'dataSendGGSheet' => "",
                'title' => "",
                'discordWebhook' => "",
                'content' => "",
                'sheetID' => "",
                'pageName' => "",
                'dataSheet' => "",
            ];

            if (isset($vnx_sync_telegram_sheet_setting[$formName])) {
                $formSetting = $vnx_sync_telegram_sheet_setting[$formName];
                $result = [
                    'dataSendDiscord' => $this->initDataSendDiscord(
                        $vnx_sync_telegram_sheet_setting[$formName]['content'],
                        $vnx_sync_telegram_sheet_setting[$formName]['title'],
                        $record
                    ),

                    'dataSendGGSheet' => $this->initDataSendGGSheet(
                        $vnx_sync_telegram_sheet_setting[$formName]['dataSheet'],
                        $record
                    ),


                    // Form cau hinh truoc khi co Discord khong co key nay.
                    'discordWebhook' => array_values(array_filter(array_map('trim', explode(',', $formSetting['discordWebhook'] ?? '')))),
                    'content' => array_filter(array_map('trim', explode(',', $vnx_sync_telegram_sheet_setting[$formName]['content']))),
                    'sheetID' => array_filter(array_map('trim', explode(',', $vnx_sync_telegram_sheet_setting[$formName]['sheetID']))),
                    'pageName' => array_filter(array_map('trim', explode(',', $vnx_sync_telegram_sheet_setting[$formName]['pageName']))),
                ];
            } else {

                // Ghi nhan form moi voi cau hinh trong. Luu dung bo key ma view va handler
                // dung, khong luu 'dataSend*' cua mang tra ve. roomID khong con dung o center
                // nhung van ghi vi vietnix-plugin doc chung option nay.
                $vnx_sync_telegram_sheet_setting[$formName] = array(
                    'referrer' => $referrer,
                    'roomID' => "",
                    'discordWebhook' => "",
                    'sheetID' => "",
                    'pageName' => "",
                    'title' => "",
                    'content' => "",
                    'dataSheet' => "",
                );

                $convertedJson = json_encode($vnx_sync_telegram_sheet_setting);

                if ($dataOption === false) {
                    add_option($option_name, $convertedJson, "", "yes");
                } else {
                    update_option($option_name, $convertedJson);
                }
            }

            return $result;

        } catch (\Throwable $e) {
            require_once VNX_PLUGIN_PATH_CENTER . '/functions/requires/push_logs_error.php';
            $push_log = new Vnx_Push_Logger_Center();
            $push_log->vnxPushLogger($e->getMessage(), ['File' => __FILE__, 'Line' => $e->getLine()]);
            throw $e;
        }
    }

    /**
     * Title + noi dung "Nhan: gia tri" cho Discord, du lieu khach nhap escape markdown Discord.
     */
    private function initDataSendDiscord($formContent, $title, $record)
    {
        require_once VNX_PLUGIN_PATH_CENTER . '/extentions/helper/discord_notify.php';

        $lines = [];
        foreach ($this->buildContentFields($formContent, $record) as [$label, $dataRecord]) {
            $lines[] = '**' . vnx_discord_escape_Center($label) . ':** ' . vnx_discord_escape_Center($dataRecord);
        }

        return [
            'title' => "🔔 " . $this->parseTitle($title),
            'content' => implode("\n", $lines),
        ];
    }

    private function parseTitle($title)
    {
        // array_filter GIU NGUYEN key cu, nen phan tu dau bi loai la mat luon key 0
        // -> phai array_values truoc khi lay [0], khong thi "Undefined array key 0".
        $title = array_values(array_filter(array_map('trim', explode(',', $title))));
        return $title[0] ?? '';
    }

    /**
     * "Ho ten: name, SĐT: phone" -> [['Ho ten', <gia tri field name>], ...], bo qua gia tri rong.
     * Tra ve cap [nhan, gia tri] chu khong dung nhan lam key: 2 dong trung nhan van giu du.
     */
    private function buildContentFields($formContent, $record)
    {
        $fields = [];
        foreach (array_filter(array_map('trim', explode(',', $formContent))) as $value) {
            // convert "Ho ten: name" to ["Ho ten", "name"]
            $value = array_values(array_filter(array_map('trim', explode(':', $value))));
            // Thieu ve trai hoac ve phai (vd. "name" khong co dau hai cham) thi bo qua.
            if (count($value) < 2) {
                continue;
            }
            // "date" khong phai field cua form: tu dien ngay gui, giong ben Google Sheet.
            $dataRecord = $value[1] === 'date' && !isset($record['date'])
                ? date("d/m/Y")
                : $this->getDataRecord($value[1], $record);
            if ($dataRecord) {
                $fields[] = [$value[0], $dataRecord];
            }
        }

        return $fields;
    }

    private function initDataSendGGSheet($formDataSheet, $record)
    {
        // formDataSheet = array input content
        $formDataSheet = array_filter(array_map('trim', explode(',', $formDataSheet)));

        $dataSendGGSheet = array(
            'majorDimension' => 'ROWS',
            "values" => [
                [
                    '=row()-1',
                ]
            ]
        );

        foreach ($formDataSheet as $value) {

            if ($value == "date") {
                $date_up = date("d/m/Y");
                array_push($dataSendGGSheet["values"][0], $date_up);
            } else {
                $dataRecord = $this->getDataRecord($value, $record);
                array_push($dataSendGGSheet["values"][0], $dataRecord);
            }
        }

        return $dataSendGGSheet;
    }
    private function getDataRecord($name, $record)
    {
        // Form Bricks thuong dat ten field "form_field_name", "form_field_phone"... trong khi
        // goi y trong tool la "name", "phone": khong co key goc thi thu them tien to form_field_.
        if (strpos($name, "-checkbox")) {
            $name = str_replace("-checkbox", "", $name);
            $dataRecord = $this->joinValues(wp_unslash($_POST[$name] ?? ($_POST['form_field_' . $name] ?? [])));
        } else {
            $dataRecord = $record[$name] ?? ($record['form_field_' . $name] ?? "");
            // Select nhieu lua chon / checkbox khong khai bao hau to -checkbox: tranh ra chu "Array".
            if (is_array($dataRecord)) {
                $dataRecord = $this->joinValues($dataRecord);
            }
        }
        return $dataRecord;
    }

    /**
     * Checkbox co the long nhieu cap (vd. name="form_field_author[][]") -> duoi phang, bo gia tri rong.
     */
    private function joinValues($values)
    {
        $flat = [];
        $values = (array) $values;
        array_walk_recursive($values, function ($value) use (&$flat) {
            $value = trim((string) $value);
            if ($value !== '') {
                $flat[] = $value;
            }
        });
        return implode(' / ', $flat);
    }
}
