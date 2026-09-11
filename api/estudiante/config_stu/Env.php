<?php

class Env
{
    //Inicializar que .env no ha sido cargado
    private static $loaded = false;

    //Leer el .env
    public static function load($path = null)
    {   
        //Si ya fue cargado solo terminar funcion
        if (self::$loaded) {
            return;
        }

        //Buscar .env en la carpeta actual
        if ($path === null) {
            $path = __DIR__ . '/.env';
        }

        //Si no existe el archivo terminar funcion
        if (!file_exists($path)) {
            return;
        }

        //Leer el archivo linea por linea y comprobar que no este vacio
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        //Recorrer cada linea
        foreach ($lines as $line) {
            //Quitar espacios
            $line = trim($line);
            //Ignorar comentrios en el archivo
            if ($line === '' || strpos($line, '#') === 0) {
                continue;
            }
            //Buscar que la linea contenga un signo =
            if (strpos($line, '=') !== false) {
                //Separar nombre y valor usando como intermedio el =
                list($name, $value) = explode('=', $line, 2);
                //Quitar espacios de los valores y nombres
                $name = trim($name);
                $value = trim($value);

                //Quitar comillas simples o dobles de los valores
                if ((strpos($value, '"') === 0 && substr($value, -1) === '"') ||
                    (strpos($value, "'") === 0 && substr($value, -1) === "'")
                ) {
                    $value = substr($value, 1, -1);
                }

                //Evitar sobre escribir variables
                if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                    //Guardar la variable en PHP y Servidor para luego usarla con getenv
                    putenv("{$name}={$value}");
                    $_ENV[$name] = $value;
                    $_SERVER[$name] = $value;
                }
            }
        }
        //Cambiar estado a cargado
        self::$loaded = true;
    }

    //Obtener un valor de .env
    public static function get($key, $default = null)
    {   
        //Verificar que el .env este cargado
        self::load();
        //Buscar la variable en el entorno
        $val = getenv($key);
        //Si la variable existe se devuelve
        if ($val !== false) {
            return $val;
        }
        //Si no la encontro en getenv buscarla con $_ENV
        if (isset($_ENV[$key])) {
            return $_ENV[$key];
        }//Si tampoco esta en $_ENV buscarla en $_SERVER
        if (isset($_SERVER[$key])) {
            return $_SERVER[$key];
        }
        //Si no la encontro devolver valor por defecto
        return $default;
    }
}
