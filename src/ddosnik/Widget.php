<?php

namespace ddosnik;

use ddosnik\utils\MinecraftQuery;
use ddosnik\utils\Config;
use ddosnik\utils\UpdateWidgetThread;
use ddosnik\utils\Logger;

final class Widget {
  private const PLATFORM_MOBILE = ['PE', 'BE'];

  /** @var \ClassLoader */
  public \ClassLoader $autoloader;
  /** @var Widget */
  public static ?Widget $instance = null;
  /** @var Logger */
  public ?Logger $logger = null;
  /** @var String[][] */
  private array $links = [];
  /** @var float */
  private float $update;
  /** @var MinecraftQuery[] */
  private array $servers = [];
  /** @var Config[] */
  private array $servers_data = [];
  /** @var array */
  private array $waitingUpdateOnline = [];
  /** @var int */
  private int $interval = 60;

  public function __construct(\ClassLoader $autoloader) {
    $this->autoloader = $autoloader;
    self::$instance = $this;
    $this->logger = new Logger();
    \GlobalLogger::set($this->logger);
    $this->initServers();
    $this->updateWidget();
    while(true) {
      if (microtime(true) - $this->update >= $this->interval) {
        $this->updateWidget();
      }
    }
  }

  public function getLoader() : \ClassLoader{
    return $this->autoloader;
  }

  public function getLogger() : Logger{
    return $this->logger;
  }

  public static function getInstance() : ?Widget{
    return self::$instance;
  }

  private function initServers() {
    if (!is_dir($dir = (WIDGET_PATH . 'servers/'))) {
      @mkdir($dir);
    }

    foreach (scandir($dir) as $filePath) {
      if (strlen($filePath) < 3) continue;
      $config = new Config($dir . $filePath, Config::YAML, [
        'host' => 'example.com',
        'name' => 'ExampleServer',
        'port' => 19132,
        'vk_group' => 'https://vk.com/durov',
        'id' => 'club1',
        'peak' => 0,
        'today-online' => 0,
        'yesterday-online' => 0,
        'last-update' => 0,
        'platform' => 'PE'
      ]);
      if (filesize($dir . $filePath) < 30) $config->save();
      $this->getLogger()->info($filePath . ' loading...');
      $this->servers[($identifier = $config->get('host').':'.$config->get('port'))] = [];
      $this->servers_data[$identifier] = $config;
      $this->links[$identifier] = [$config->get('vk_group'), $config->get('id'), $config->get('name'), $config->get('platform')];
    }
    $this->getLogger()->notice(sizeof($this->servers) . ' servers is initializated.');
  }

    public function updateStats(string $identifier, int $online) : void{
      $servers_data = & $this->servers_data[$identifier];
      $servers_data->set('yesterday-online', $servers_data->get('today-online'));
      $servers_data->set('today-online', $online);
      $servers_data->set('last-update', date('d'));
      $servers_data->save();
    }

    public function updateWidget() : void{
      date_default_timezone_set('Europe/Moscow');
      $generalOnline = 0;
      $servers_online = [];
      foreach ($this->servers as $identifier => $server) { // пинг запросы к серверам.
        $address = explode(':', $identifier);
        if (in_array($this->links[$identifier][3], self::PLATFORM_MOBILE)) {
          $this->servers[$identifier] = MinecraftQuery::queryPocket($this->logger, $address[0], $address[1]);
        } else {
          $this->servers[$identifier] = MinecraftQuery::queryJava($this->logger, $address[0], $address[1]);
        }
        $data = & $this->servers[$identifier];
        $servers_data = & $this->servers_data[$identifier];

        if (isset($this->waitingUpdateOnline[$identifier]) && $data['num'] > 0) {
          unset($this->waitingUpdateOnline[$identifier]);
          $this->updateStats($identifier, $data['num']);
        }

        if (isset($data['error'])) { // проверка на доступность
          $data['motd'] = $this->links[$identifier][2] . ' (Нет ответа)';
        } else {
          $data['motd'] = $this->links[$identifier][2];
        }

      if ((($date = date('Hi')) >= 1500 && $date < 1505 && $servers_data->get('last-update') !== date('d')) || $servers_data->get('last-update') === 0) {
          if ($data['num'] > 0) {
            $this->updateStats($identifier, $data['num']);
          } else {
            $this->waitingUpdateOnline[$identifier] = true;
        }
      }

      if ($data['num'] > $this->servers_data[$identifier]->get('peak')) { // обновление максимального онлайна
        $servers_data->set('peak', $data['num']);
        $servers_data->save();
      }
      $generalOnline += $data['num']; // собираем общий онлайн
      $servers_online[$identifier] = $data['num']; // собираем массив для сортировки
    }

    arsort($servers_online);
    $body = '';
    foreach ($servers_online as $identifier => $online) {
      $server = $this->servers[$identifier];
      $body .= json_encode([
        [
          'text' => $server['motd'],
          'url' => $this->links[$identifier][0],
          'icon_id' => $this->links[$identifier][1],
        ],
        [
          'text' => $server['num'],
        ],
        [
          'text' => $server['max'],
        ],
        [
          'text' => $this->servers_data[$identifier]->get('peak'),
        ],
        [
          'text' => round((100 - (($this->servers_data[$identifier]->get('yesterday-online') * 100) / $this->servers_data[$identifier]->get('today-online'))), 2).'%'
        ]
      ], JSON_UNESCAPED_UNICODE). ',' . PHP_EOL;
    }
    $table = [
      'type' => 'table',
      'v' => 5.173,
      'access_token' => 'vk1.a.3BI7eAxUrYTk3DxWFT8my0pv_SXRC0m60DiPjRq--VcFKnUnl1depPhgQEhuBVK4BRnR1WdbY_01PzZ5-KyUNYkNjvex-wQ92RvQf_T3VZbxQvOmZNWXJ6E4pjqr_7XhlTNnJwAz9eUhdFG61fqB-MVNqcsbdP9CGPUWqeVtJaUvD0ECI0JUJkK2afqghUrZ',
      'code' => '
      return {
        "title": "Общий онлайн — '.$generalOnline.'",
        "title_url": "https://vk.com/showyouass",
        "title_counter": '.sizeof($this->servers).',
        "head": [{
          "text": "Проект"
          },{
          "text": "Онлайн",
          "align": "center"
          }, {
          "text": "Слоты",
          "align": "center"
          }, {
          "text": "Пик",
          "align": "center"
          }, {
          "text": "За сутки",
          "align": "center"
        }],
        "body": [
          '.$body.'
        ]
      };
      '];
      $thread = new UpdateWidgetThread($table);
      $thread->start(); $thread->join();
      $thread->quit();
      if ($thread->error) {
        $this->getLogger()->error('Произошла ошибка при подключении к серверу VK.');
        $this->update = 0;
        return;
      }
      $this->update = microtime(true);
      $this->getLogger()->info('Таблица виджета отправлена на сервер VK.');
    }
}