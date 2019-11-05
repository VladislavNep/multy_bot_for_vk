<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use VK\Client\VKApiClient;

class HomeController extends Controller
{
//    /**
//     * Create a new controller instance.
//     *
//     * @return void
//     */
//    public function __construct()
//    {
//        $this->middleware('auth');
//    }

    /**
     * @return string
     */
    public function index()
    {
        header("HTTP/1.1 200 OK");
        $vk_callback_event =  json_decode(file_get_contents("php://input"), true);
        $this -> getlog(json_encode($vk_callback_event));

        if ($vk_callback_event['secret'] !== getenv('VK_SECRET_TOKEN')) {
            return response('nioh');
        }


            switch ($vk_callback_event['type']){
                case 'confirmation':
                    return response(getenv('VK_CONFIRMATION_CODE'));
                    break;

                case 'message_new':
                    try {
                        $object = $vk_callback_event['object'] ?? [];
                        $user_id = $object['from_id'] ?? 0;
                        $txt = $object['text'] ?? "";


                        // получаю его имя
                        $vk = new VKApiClient('5.103');
                        $response = $vk->users()->get(getenv('VK_TOKEN'), array(
                            'user_ids' => [$user_id],
                        ));
                        $name = $response[0]['first_name'];


                        //главное меню
                        $keyboard_index =
                            [
                                "one_time" => false,
                                "buttons" => [
                                    [
                                        [
                                            "action" => [
                                                "type" => "text",
                                                "payload" => "{\"button\": \"speech_recognition\"}",
                                                "label" => "Распознование речи"
                                            ],
                                            "color" => "positive"
                                        ],
                                        [
                                            "action" => [
                                                "type" => "text",
                                                "payload" => "{\"button\": \"speech_synthesis\"}",
                                                "label" => "🤖Синтез речи"
                                            ],
                                            "color" => "positive"
                                        ],
                                    ],
                                    [
                                        [
                                            "action" => [
                                                "type" => "text",
                                                "payload" => "{\"button\": \"history_day\"}",
                                                "label" => "История дня"
                                            ],
                                            "color" => "positive"
                                        ],
                                    ]
                                ]

                            ];

                        // меню клавиатура синтеза речи
                        $keyboard_speech_synthesis =
                            [
                                "one_time" => false,
                                "buttons" => [
                                    [
                                        [
                                            "action" => [
                                                "type" => "text",
                                                "payload" => "{\"button\": \"voice\"}",
                                                "label" => "Сменить голос"
                                            ],
                                            "color" => "positive"
                                        ]
                                    ],
                                    [
                                        [
                                            "action" => [
                                                "type" => "text",
                                                "payload" => "{\"button\": \"back_index\"}",
                                                "label" => "🔙 🤖Назад"
                                            ],
                                            "color" => "negative"
                                        ],
                                    ]
                                ]

                            ];

                        // меню клавиатура синтеза речи для смены голоса
                        $keyboard_speech_synthesis_voice =
                            [
                                "one_time" => false,
                                "buttons" => [
//                [
//                    [
//                        "action" => [
//                            "type" => "text",
//                            "payload" => "{\"button\": \"voice_man\"}",
//                            "label" => "🗣 Мужчина"
//                        ],
//                        "color" => "positive"
//                    ],
//                    [
//                        "action" => [
//                            "type" => "text",
//                            "payload" => "{\"button\": \"voice_woman\"}",
//                            "label" => "🗣 Женщина"
//                        ],
//                        "color" => "positive"
//                    ]
//                ],
                                    [
                                        [
                                            "action" => [
                                                "type" => "text",
                                                "payload" => "{\"button\": \"back_speech_synthesis\"}",
                                                "label" => "🔙 🤖Назад"
                                            ],
                                            "color" => "negative"
                                        ],
                                    ]
                                ]

                            ];

                        // клавиатура распознования речи
                        $keyboard_speech_recognition =
                            [
                                "one_time" => false,
                                "buttons" => [
                                    [
                                        [
                                            "action" => [
                                                "type" => "text",
                                                "payload" => "{\"button\": \"speech_recognition_instructions\"}",
                                                "label" => "Как добавить бота в беседу"
                                            ],
                                            "color" => "positive"
                                        ],

                                    ],
                                    [
                                        [
                                            "action" => [
                                                "type" => "text",
                                                "payload" => "{\"button\": \"back_index\"}",
                                                "label" => "🔙 🤖Назад"
                                            ],
                                            "color" => "negative"
                                        ],
                                    ]
                                ]

                            ];



                        if (isset($object['payload'])) {
                            $payload = json_decode($object['payload'], true);
                            $value_button = $payload['button'];

                        switch ($value_button) {
                            case  "start" :
                                $message = "Добро пожаловать $name! \n Я Мульти голосовой бот, разработчик [vladislav_nep | Непомнящих Владислав], у меня есть свой сайт, его найдете в ссылках. \n Что я умею: \n 1️⃣ переводить текст в голосовые сообщения  \n 2️⃣ Менять голос \n 3️⃣ Переводить голосовые сообщения в текст \n 4️⃣ Добавлять в чаты для автоматического перевода голосовых сообщений в текст \n 5️⃣ Повесилить вас историей для! \n Если не видите кнопок, то используйте цифры как команды. \n \n Надеюсь я вам помогу или доставлю удовольствие!";
                                $send_value_keyboard = $keyboard_index;
                                break;

                            case  "speech_recognition" :
                                switch ($value_button) {
                                    case "back_index" :
                                        $message = "";
                                        $send_value_keyboard = $keyboard_index;
                                        break;
                                    case "speech_recognition_instructions":
                                        $message = "Здесь будет инструкция, пока лень писать)";
                                        $send_value_keyboard = $keyboard_speech_recognition;
                                        break;

                                    default:
                                        $message = "Отправьте голосовое сообщение до 30 секунд! В разработке)";
                                        $send_value_keyboard = $keyboard_speech_recognition;
                                        break;
                                }
                                break;

                            case  "speech_synthesis" :
                                switch ($value_button) {
                                    case "back_index" :
                                        $message = "";
                                        $send_value_keyboard = $keyboard_index;
                                        break;
                                    case "voice" :
                                        $message = "Смена голоса будет доступна в последнию очередь";
                                        $send_value_keyboard = $keyboard_speech_synthesis_voice;
                                        break;
                                    case "back_speech_synthesis":
                                        $send_value_keyboard = $keyboard_speech_synthesis;
                                        break;
                                    default:
                                        $message = "Синтез речи запущен, в разработке)";
                                        $send_value_keyboard = $keyboard_speech_synthesis;
                                }
                                break;

                                case  "history_day" :
                                    $message = "В разработке";
                                        $send_value_keyboard = "";
                                        break;


                                    default:
                                        $message = "Я вас не понял! Почему? \n 1) Команды осуществляются только при помощи кнопок \n 2) Слишком длинный текст для синтеза речи \n 3) Аудио длиннее 30 сек для распознования речи";
                                        $send_value_keyboard = $keyboard_index;
                                        break;
                                    }
                        } else {
                            $message = "Я реагирую только на кнопки!";
                            $send_value_keyboard = $keyboard_index;

                        }

                                        // отправляем сообщение
                                        $vk = new VKApiClient('5.103');
                                        $response = $vk->messages()->send(getenv('VK_TOKEN'), array(
                                            'user_id' => $user_id,
                                            'message' => $message,
                                            'keyboard' => json_encode($send_value_keyboard),
                                            'random_id' => rand(),
                                        ));
                                        echo 'ok';
                                        break;

                    } catch (\VK\Exceptions\VKApiException $e){
                        $this -> getlog($e -> getMessage());
                    }

                  }

    }

    function getlog($msg){
        file_put_contents('php://stdout', $msg. "\n");
    }

}
