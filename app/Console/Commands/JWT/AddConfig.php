<?php

namespace App\Console\Commands\JWT;

use Illuminate\Console\Command;

class AddConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jwt:conf';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate secret key & create config';

    public static function random($length = 16)
    {
        $string = '';
        while (($len = strlen($string)) < $length) {
            $size = $length - $len;
            $bytesSize = (int) ceil($size / 3) * 3;
            $bytes = random_bytes($bytesSize);
            $string .= substr(str_replace(
                ['/', '+', '='],
                '',
                base64_encode($bytes)
            ), 0, $size);
        }
        return $string;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $file = base_path() . '/.env';
        $key = self::random(64);
        $config =
            "
JWT_ISSUER=http://example.com
JWT_SECRET_KEY=$key
JWT_ACCESS_TTL=30
JWT_REFRESH_TTL=43200
JWT_LEEWAY=60
";



        $openedFile = fopen($file, 'r') or die('Не удалось открыть файл ".env"' . PHP_EOL);
        while (!feof($openedFile)) {
            $str = fgets($openedFile);
            if (preg_match('/(JWT_ISSUER|JWT_SECRET_KEY|JWT_ACCESS_TTL|JWT_REFRESH_TTL|JWT_LEEWAY)/', $str)) {
                die('Обнаружены уже добавленные настройки в файле ".env". Если вы хотите добавить их заново - удалите существующие.' . PHP_EOL);
            }
        }
        fclose($openedFile);

        file_put_contents($file, $config, FILE_APPEND);

        echo 'Настройки добавлены в файл ".env"' . PHP_EOL;
    }
}
