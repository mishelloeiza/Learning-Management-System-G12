<?php

class Response
{
    /*static function success($message, $data, $code, $otherData = null)
    {
        header("Content-Type: application/json");
        $response = [
            "code" => $code,
            "message" => $message,
            "data" => $data,
        ];
        if ($otherData) {
            foreach ($otherData as $key => $value) {
                $response[$key] = $value;
            }
        }
        exit(json_encode($response));
    }*/
    
    //Funcion de mensajes de exito solo devuelve mensaje y codigo
    static function success($message, $code, $otherData = null)
    {
        header("Content-Type: application/json");
        $response = [
            "code" => $code,
            "message" => $message
        ];
        if ($otherData) {
            foreach ($otherData as $key => $value) {
                $response[$key] = $value;
            }
        }
        exit(json_encode($response));
    }

    //Funcion de mensajes de error solo devuelve mensaje y codigo
    static function error($message, $code, $codeMethod)
    {
        header("Content-Type: application/json");
        http_response_code($codeMethod);
        exit(json_encode([
            "code" => $code,
            "message" => $message
        ]));
    }

    //Funcion de errores de desarrolladores
    static function debug($error = null, $errorTh = null)
    {
        if ($error) {
            exit(json_encode($error));
        } else {
            exit(json_encode($errorTh->getMessage()));
        }
    }
}
